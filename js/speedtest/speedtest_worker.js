var testState = -1;
var dlStatus = "";
var ulStatus = "";
var pingStatus = "";
var jitterStatus = "";
var clientIp = "";
var dlProgress = 0;
var ulProgress = 0;
var pingProgress = 0;
var testId = null;

var log = "";
function tlog(s) {
  if (settings.telemetry_level >= 2) {
    log += Date.now() + ": " + s + "\n";
  }
}
function tverb(s) {
  if (settings.telemetry_level >= 3) {
    log += Date.now() + ": " + s + "\n";
  }
}
function twarn(s) {
  if (settings.telemetry_level >= 2) {
    log += Date.now() + " WARN: " + s + "\n";
  }
}

// test settings. can be overridden by sending specific values with the start command
var settings = {
  mpot: false, //set to true when in MPOT mode
  test_order: "IP_D_U", //order in which tests will be performed as a string. D=Download, U=Upload, P=Ping+Jitter, I=IP, _=1 second delay
  time_ul_max: 15, // max duration of upload test in seconds
  time_dl_max: 15, // max duration of download test in seconds
  time_auto: true, // if set to true, tests will take less time on faster connections
  time_ulGraceTime: 3, //time to wait in seconds before actually measuring ul speed (wait for buffers to fill)
  time_dlGraceTime: 1.5, //time to wait in seconds before actually measuring dl speed (wait for TCP window to increase)
  count_ping: 10, // number of pings to perform in ping test
  url_dl: "https://monsieur-wifi.com/librespeed/backend/garbage.php", // path to a large file or garbage.php, used for download test. must be relative to this js file
  url_ul: "https://monsieur-wifi.com/librespeed/backend/empty.php", // path to an empty file, used for upload test. must be relative to this js file
  url_ping: "https://monsieur-wifi.com/librespeed/backend/empty.php", // path to an empty file, used for ping test. must be relative to this js file
  url_getIp: "https://monsieur-wifi.com/librespeed/backend/getIP.php", // path to getIP.php relative to this js file, or a similar thing that outputs the client's ip
  getIp_ispInfo: true, //if set to true, the server will include ISP info with the IP address
  getIp_ispInfo_distance: "km", //km or mi=estimate distance from server in km/mi; set to false to disable distance estimation. getIp_ispInfo must be enabled in order for this to work
  xhr_dlMultistream: 6, // number of download streams to use (can be different if enable_quirks is active)
  xhr_ulMultistream: 3, // number of upload streams to use (can be different if enable_quirks is active)
  xhr_multistreamDelay: 300, //how much concurrent requests should be delayed
  xhr_ignoreErrors: 1, // 0=fail on errors, 1=attempt to restart a stream if it fails, 2=ignore all errors
  xhr_dlUseBlob: false, // if set to true, it reduces ram usage but uses the hard drive (useful with large garbagePhp_chunkSize and/or high xhr_dlMultistream)
  xhr_ul_blob_megabytes: 20, //size in megabytes of the upload blobs sent in the upload test (forced to 4 on chrome mobile)
  garbagePhp_chunkSize: 100, // size of chunks sent by garbage.php (can be different if enable_quirks is active)
  enable_quirks: true, // enable quirks for specific browsers. currently it overrides settings to optimize for specific browsers, unless they are already being overridden with the start command
  ping_allowPerformanceApi: true, // if enabled, the ping test will attempt to calculate the ping more precisely using the Performance API. Currently works perfectly in Chrome, badly in Edge, and not at all in Firefox. If Performance API is not supported or the result is obviously wrong, a fallback is provided.
  overheadCompensationFactor: 1.06, //can be changed to compensatie for transport overhead. (see doc.md for some other values)
  useMebibits: false, //if set to true, speed will be reported in mebibits/s instead of megabits/s
  telemetry_level: 0, // 0=disabled, 1=basic (results only), 2=full (results and timing) 3=debug (results+log)
  url_telemetry: "results/telemetry.php", // path to the script that adds telemetry data to the database
  telemetry_extra: "", //extra data that can be passed to the telemetry through the settings
    forceIE11Workaround: false //when set to true, it will foce the IE11 upload test on all browsers. Debug only
};

var xhr = null;
var interval = null;
var test_pointer = 0;

function url_sep(url) {
  return url.match(/\?/) ? "&" : "?";
}

this.addEventListener("message", function(e) {
  var params = e.data.split(" ");
  if (params[0] === "status") {
    postMessage(
      JSON.stringify({
        testState: testState,
        dlStatus: dlStatus,
        ulStatus: ulStatus,
        pingStatus: pingStatus,
        clientIp: clientIp,
        jitterStatus: jitterStatus,
        dlProgress: dlProgress,
        ulProgress: ulProgress,
        pingProgress: pingProgress,
        testId: testId
      })
    );
  }
  if (params[0] === "start" && testState === -1) {
    testState = 0;
    try {
      var s = {};
      try {
        var ss = e.data.substring(5);
        if (ss) {
          s = JSON.parse(ss);
        }
      } catch (e) {
        twarn("Error parsing custom settings JSON. Please check your syntax");
      }
      for (var key in s) {
        if (typeof settings[key] !== "undefined") {
          settings[key] = s[key];
        } else twarn("Unknown setting ignored: " + key);
      }
      var ua = navigator.userAgent;
      if (settings.enable_quirks || (typeof s.enable_quirks !== "undefined" && s.enable_quirks)) {
        if (/Firefox.(\d+\.\d+)/i.test(ua)) {
          if (typeof s.ping_allowPerformanceApi === "undefined") {
            settings.ping_allowPerformanceApi = false;
          }
        }
        if (/Edge.(\d+\.\d+)/i.test(ua)) {
          if (typeof s.xhr_dlMultistream === "undefined") {
            settings.xhr_dlMultistream = 3;
          }
        }
        if (/Chrome.(\d+)/i.test(ua) && !!self.fetch) {
          if (typeof s.xhr_dlMultistream === "undefined") {
            settings.xhr_dlMultistream = 5;
          }
        }
      }
      if (/Edge.(\d+\.\d+)/i.test(ua)) {
        settings.forceIE11Workaround = true;
      }
      if (/PlayStation 4.(\d+\.\d+)/i.test(ua)) {
        settings.forceIE11Workaround = true;
      }
      if (/Chrome.(\d+)/i.test(ua) && /Android|iPhone|iPad|iPod|Windows Phone/i.test(ua)) {
        settings.xhr_ul_blob_megabytes = 4;
      }
      if (/^((?!chrome|android|crios|fxios).)*safari/i.test(ua)) {
        settings.forceIE11Workaround = true;
      }
      if (typeof s.telemetry_level !== "undefined") {
        settings.telemetry_level = s.telemetry_level === "basic" ? 1 : s.telemetry_level === "full" ? 2 : s.telemetry_level === "debug" ? 3 : 0;
      }
      settings.test_order = settings.test_order.toUpperCase();
    } catch (e) {
      twarn("Possible error in custom test settings. Some settings might not have been applied. Exception: " + e);
    }
    tverb(JSON.stringify(settings));
    test_pointer = 0;
    var iRun = false, dRun = false, uRun = false, pRun = false;
    var runNextTest = function() {
      if (testState == 5) {
        return;
      }
      if (test_pointer >= settings.test_order.length) {
        if (settings.telemetry_level > 0) {
          sendTelemetry(function(id) {
            testState = 4;
            if (id != null) {
              testId = id;
            }
          });
        } else {
          testState = 4;
        }
        return;
      }
      switch (settings.test_order.charAt(test_pointer)) {
        case "I":
          {
            test_pointer++;
            if (iRun) {
              runNextTest();
              return;
            } else iRun = true;
            getIp(runNextTest);
          }
          break;
        case "D":
          {
            test_pointer++;
            if (dRun) {
              runNextTest();
              return;
            } else dRun = true;
            testState = 1;
            dlTest(runNextTest);
          }
          break;
        case "U":
          {
            test_pointer++;
            if (uRun) {
              runNextTest();
              return;
            } else uRun = true;
            testState = 3;
            ulTest(runNextTest);
          }
          break;
        case "P":
          {
            test_pointer++;
            if (pRun) {
              runNextTest();
              return;
            } else pRun = true;
            testState = 2;
            pingTest(runNextTest);
          }
          break;
        case "_":
          {
            test_pointer++;
            setTimeout(runNextTest, 1000);
          }
          break;
        default:
          test_pointer++;
      }
    };
    runNextTest();
  }
  if (params[0] === "abort") {
    if (testState >= 4) {
      return;
    }
    tlog("manually aborted");
    clearRequests();
    runNextTest = null;
    if (interval) {
      clearInterval(interval);
    }
    if (settings.telemetry_level > 1) {
      sendTelemetry(function() {});
    }
    testState = 5;
    dlStatus = "";
    ulStatus = "";
    pingStatus = "";
    jitterStatus = "";
    clientIp = "";
    dlProgress = 0;
    ulProgress = 0;
    pingProgress = 0;
  }
});

function clearRequests() {
  tverb("stopping pending XHRs");
  if (xhr) {
    for (var i = 0; i < xhr.length; i++) {
      try {
        xhr[i].onprogress = null;
        xhr[i].onload = null;
        xhr[i].onerror = null;
      } catch (e) {}
      try {
        xhr[i].upload.onprogress = null;
        xhr[i].upload.onload = null;
        xhr[i].upload.onerror = null;
      } catch (e) {}
      try {
        xhr[i].abort();
      } catch (e) {}
      try {
        delete xhr[i];
      } catch (e) {}
    }
    xhr = null;
  }
}

var ipCalled = false;
var ispInfo = "";

function getIp(done) {
  tverb("getIp");
  if (ipCalled) {
    return;
  } else {
    ipCalled = true;
  }
  var startT = new Date().getTime();
  xhr = new XMLHttpRequest();
  xhr.onload = function() {
    tlog("IP: " + xhr.responseText + ", took " + (new Date().getTime() - startT) + "ms");
    try {
      var data = JSON.parse(xhr.responseText);
      clientIp = data.processedString;
      ispInfo = data.rawIspInfo;
    } catch (e) {
      clientIp = xhr.responseText;
      ispInfo = "";
    }
    done();
  };
  xhr.onerror = function() {
    tlog("getIp failed, took " + (new Date().getTime() - startT) + "ms");
    done();
  };
  xhr.open("GET", settings.url_getIp + url_sep(settings.url_getIp) + (settings.mpot ? "cors=true&" : "") + (settings.getIp_ispInfo ? "isp=true" + (settings.getIp_ispInfo_distance ? "&distance=" + settings.getIp_ispInfo_distance + "&" : "&") : "&") + "r=" + Math.random(), true);
  xhr.send();
}

var dlCalled = false;

function dlTest(done) {
  tverb("dlTest");
  if (dlCalled) {
    return;
  } else {
    dlCalled = true;
  }
  var totLoaded = 0.0, startT = new Date().getTime(), bonusT = 0, graceTimeDone = false, failed = false;
  xhr = [];
  var testStream = function(i, delay) {
    setTimeout(function() {
      if (testState !== 1) {
        return;
      }
      tverb("dl test stream started " + i + " " + delay);
      var prevLoaded = 0;
      var x = new XMLHttpRequest();
      xhr[i] = x;
      xhr[i].onprogress = function(event) {
        tverb("dl stream progress event " + i + " " + event.loaded);
        if (testState !== 1) {
          try {
            x.abort();
          } catch (e) {}
        }
        var loadDiff = event.loaded <= 0 ? 0 : event.loaded - prevLoaded;
        if (isNaN(loadDiff) || !isFinite(loadDiff) || loadDiff < 0) {
          return;
        }
        totLoaded += loadDiff;
        prevLoaded = event.loaded;
      }.bind(this);
      xhr[i].onload = function() {
        tverb("dl stream finished " + i);
        try {
          xhr[i].abort();
        } catch (e) {}
        testStream(i, 0);
      }.bind(this);
      xhr[i].onerror = function() {
        tverb("dl stream failed " + i);
        if (settings.xhr_ignoreErrors === 0) {
          failed = true;
        }
        try {
          xhr[i].abort();
        } catch (e) {}
        delete xhr[i];
        if (settings.xhr_ignoreErrors === 1) {
          testStream(i, 0);
        }
      }.bind(this);
        try {
          if (settings.xhr_dlUseBlob) {
            xhr[i].responseType = "blob";
          } else {
            xhr[i].responseType = "arraybuffer";
          }
        } catch (e) {}
        xhr[i].open("GET", settings.url_dl + url_sep(settings.url_dl) + (settings.mpot ? "cors=true&" : "") + "r=" + Math.random() + "&ckSize=" + settings.garbagePhp_chunkSize, true);
        xhr[i].send();
    }.bind(this), 1 + delay);
  }.bind(this);
  for (var i = 0; i < settings.xhr_dlMultistream; i++) {
    testStream(i, settings.xhr_multistreamDelay * i);
  }
  interval = setInterval(function() {
    tverb("DL: " + dlStatus + (graceTimeDone ? "" : " (in grace time)"));
    var t = new Date().getTime() - startT;
    if (graceTimeDone) {
      dlProgress = (t + bonusT) / (settings.time_dl_max * 1000);
    }
    if (t < 200) {
      return;
    }
    if (!graceTimeDone) {
      if (t > 1000 * settings.time_dlGraceTime) {
        if (totLoaded > 0) {
          startT = new Date().getTime();
          bonusT = 0;
          totLoaded = 0.0;
        }
        graceTimeDone = true;
      }
    } else {
      var speed = totLoaded / (t / 1000.0);
      if (settings.time_auto) {
        var bonus = (5.0 * speed) / 100000;
        bonusT += bonus > 400 ? 400 : bonus;
      }
      dlStatus = ((speed * 8 * settings.overheadCompensationFactor) / (settings.useMebibits ? 1048576 : 1000000)).toFixed(2);
      if ((t + bonusT) / 1000.0 > settings.time_dl_max || failed) {
        if (failed || isNaN(dlStatus)) {
          dlStatus = "Fail";
        }
        clearRequests();
        clearInterval(interval);
        dlProgress = 1;
        tlog("dlTest: " + dlStatus + ", took " + (new Date().getTime() - startT) + "ms");
        done();
      }
    }
  }.bind(this), 200);
}

var ulCalled = false;

function ulTest(done) {
  tverb("ulTest");
  if (ulCalled) {
    return;
  } else {
    ulCalled = true;
  }
  var r = new ArrayBuffer(1048576);
  var maxInt = Math.pow(2, 32) - 1;
  try {
    r = new Uint32Array(r);
    for (var i = 0; i < r.length; i++) {
      r[i] = Math.random() * maxInt;
    }
  } catch (e) {}
  var req = [];
  var reqsmall = [];
  for (var i = 0; i < settings.xhr_ul_blob_megabytes; i++) {
    req.push(r);
  }
  req = new Blob(req);
  r = new ArrayBuffer(262144);
  try {
    r = new Uint32Array(r);
    for (var i = 0; i < r.length; i++) {
      r[i] = Math.random() * maxInt;
    }
  } catch (e) {}
  reqsmall.push(r);
  reqsmall = new Blob(reqsmall);
  var testFunction = function() {
    var totLoaded = 0.0, startT = new Date().getTime(), bonusT = 0, graceTimeDone = false, failed = false;
    xhr = [];
    var testStream = function(i, delay) {
      setTimeout(function() {
          if (testState !== 3) {
            return;
          }
          tverb("ul test stream started " + i + " " + delay);
          var prevLoaded = 0;
          var x = new XMLHttpRequest();
          xhr[i] = x;
          var ie11workaround;
          if (settings.forceIE11Workaround) ie11workaround = true;
          else {
            try {
              xhr[i].upload.onprogress;
              ie11workaround = false;
            } catch (e) {
              ie11workaround = true;
            }
          }
          if (ie11workaround) {
            xhr[i].onload = xhr[i].onerror = function() {
              tverb("ul stream progress event (ie11wa)");
              totLoaded += reqsmall.size;
              testStream(i, 0);
            };
            xhr[i].open("POST", settings.url_ul + url_sep(settings.url_ul) + (settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
            try {
              xhr[i].setRequestHeader("Content-Encoding", "identity");
            } catch (e) {}
            xhr[i].send(reqsmall);
          } else {
            xhr[i].upload.onprogress = function(event) {
              tverb("ul stream progress event " + i + " " + event.loaded);
              if (testState !== 3) {
                try {
                  x.abort();
                } catch (e) {}
              }
              var loadDiff = event.loaded <= 0 ? 0 : event.loaded - prevLoaded;
              if (isNaN(loadDiff) || !isFinite(loadDiff) || loadDiff < 0) {
                return;
              }
              totLoaded += loadDiff;
              prevLoaded = event.loaded;
            }.bind(this);
            xhr[i].upload.onload = function() {
              tverb("ul stream finished " + i);
              testStream(i, 0);
            }.bind(this);
            xhr[i].upload.onerror = function() {
              tverb("ul stream failed " + i);
              if (settings.xhr_ignoreErrors === 0) {
                failed = true;
              }
              try {
                xhr[i].abort();
              } catch (e) {}
              delete xhr[i];
              if (settings.xhr_ignoreErrors === 1) {
                testStream(i, 0);
              }
            }.bind(this);
            xhr[i].open("POST", settings.url_ul + url_sep(settings.url_ul) + (settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
            try {
              xhr[i].setRequestHeader("Content-Encoding", "identity");
            } catch (e) {}
            xhr[i].send(req);
          }
        }.bind(this), delay);
    }.bind(this);
    for (var i = 0; i < settings.xhr_ulMultistream; i++) {
      testStream(i, settings.xhr_multistreamDelay * i);
    }
    interval = setInterval(function() {
        tverb("UL: " + ulStatus + (graceTimeDone ? "" : " (in grace time)"));
        var t = new Date().getTime() - startT;
        if (graceTimeDone) ulProgress = (t + bonusT) / (settings.time_ul_max * 1000);
        if (t < 200) return;
        if (!graceTimeDone) {
          if (t > 1000 * settings.time_ulGraceTime) {
            if (totLoaded > 0) {
              startT = new Date().getTime();
              bonusT = 0;
              totLoaded = 0.0;
            }
            graceTimeDone = true;
          }
        } else {
          var speed = totLoaded / (t / 1000.0);
          if (settings.time_auto) {
            var bonus = (5.0 * speed) / 100000;
            bonusT += bonus > 400 ? 400 : bonus;
          }
          ulStatus = ((speed * 8 * settings.overheadCompensationFactor) / (settings.useMebibits ? 1048576 : 1000000)).toFixed(2);
          if ((t + bonusT) / 1000.0 > settings.time_ul_max || failed) {
            if (failed || isNaN(ulStatus)) ulStatus = "Fail";
            clearRequests();
            clearInterval(interval);
            ulProgress = 1;
            tlog("ulTest: " + ulStatus + ", took " + (new Date().getTime() - startT) + "ms");
            done();
          }
        }
      }.bind(this),
      200
    );
  }.bind(this);
  if (settings.mpot) {
    tverb("Sending POST request before performing upload test");
    xhr = [];
    xhr[0] = new XMLHttpRequest();
    xhr[0].onload = xhr[0].onerror = function() {
      tverb("POST request sent, starting upload test");
      testFunction();
    }.bind(this);
    xhr[0].open("POST", settings.url_ul);
    xhr[0].send();
  } else {
    testFunction();
  }
}

var ptCalled = false;

function pingTest(done) {
  tverb("pingTest");
  if (ptCalled) {
    return;
  } else {
    ptCalled = true;
  }
  var startT = new Date().getTime();
  var prevT = null;
  var ping = 0.0;
  var jitter = 0.0;
  var i = 0;
  var prevInstspd = 0;
  xhr = [];
  var doPing = function() {
    tverb("ping");
    pingProgress = i / settings.count_ping;
    prevT = new Date().getTime();
    xhr[0] = new XMLHttpRequest();
    xhr[0].onload = function() {
      tverb("pong");
      if (i === 0) {
        prevT = new Date().getTime();
      } else {
        var instspd = new Date().getTime() - prevT;
        if (settings.ping_allowPerformanceApi) {
          try {
            var p = performance.getEntries();
            p = p[p.length - 1];
            var d = p.responseStart - p.requestStart;
            if (d <= 0) {
              d = p.duration;
            }
            if (d > 0 && d < instspd) {
              instspd = d;
            }
          } catch (e) {
            tverb("Performance API not supported, using estimate");
          }
        }
        if (instspd < 1) {
          instspd = prevInstspd;
        }
        if (instspd < 1) {
          instspd = 1;
        }
        var instjitter = Math.abs(instspd - prevInstspd);
        if (i === 1) {
          ping = instspd;
        } else {
          if (instspd < ping) {
            ping = instspd;
          }
          if (i === 2) {
            jitter = instjitter;
          } else {
            jitter = instjitter > jitter ? jitter * 0.3 + instjitter * 0.7 : jitter * 0.8 + instjitter * 0.2;
          }
        }
        prevInstspd = instspd;
      }
      pingStatus = ping.toFixed(2);
      jitterStatus = jitter.toFixed(2);
      i++;
      tverb("ping: " + pingStatus + " jitter: " + jitterStatus);
      if (i < settings.count_ping) {
        doPing();
      } else {
        pingProgress = 1;
        tlog("ping: " + pingStatus + " jitter: " + jitterStatus + ", took " + (new Date().getTime() - startT) + "ms");
        done();
      }
    }.bind(this);
    xhr[0].onerror = function() {
      tverb("ping failed");
      if (settings.xhr_ignoreErrors === 0) {
        pingStatus = "Fail";
        jitterStatus = "Fail";
        clearRequests();
        tlog("ping test failed, took " + (new Date().getTime() - startT) + "ms");
        pingProgress = 1;
        done();
      }
      if (settings.xhr_ignoreErrors === 1) {
        doPing();
      }
      if (settings.xhr_ignoreErrors === 2) {
        i++;
        if (i < settings.count_ping) {
          doPing();
        } else {
          pingProgress = 1;
          tlog("ping: " + pingStatus + " jitter: " + jitterStatus + ", took " + (new Date().getTime() - startT) + "ms");
          done();
        }
      }
    }.bind(this);
    xhr[0].open("GET", settings.url_ping + url_sep(settings.url_ping) + (settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
    xhr[0].send();
  }.bind(this);
  doPing();
}

function sendTelemetry(done) {
  if (settings.telemetry_level < 1) {
    return;
  }
  xhr = new XMLHttpRequest();
  xhr.onload = function() {
    try {
      var parts = xhr.responseText.split(" ");
      if (parts[0] == "id") {
        try {
          var id = parts[1];
          done(id);
        } catch (e) {
          done(null);
        }
      } else {
        done(null);
      }
    } catch (e) {
      done(null);
    }
  };
  xhr.onerror = function() {
    done(null);
  };
  xhr.open("POST", settings.url_telemetry + url_sep(settings.url_telemetry) + (settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
  var telemetryIspInfo = {
    processedString: clientIp,
    rawIspInfo: typeof ispInfo === "object" ? ispInfo : ""
  };
  try {
    var fd = new FormData();
    fd.append("ispinfo", JSON.stringify(telemetryIspInfo));
    fd.append("dl", dlStatus);
    fd.append("ul", ulStatus);
    fd.append("ping", pingStatus);
    fd.append("jitter", jitterStatus);
    fd.append("log", settings.telemetry_level > 1 ? log : "");
    fd.append("extra", settings.telemetry_extra);
    xhr.send(fd);
  } catch (ex) {
    var postData = "extra=" + encodeURIComponent(settings.telemetry_extra) + "&ispinfo=" + encodeURIComponent(JSON.stringify(telemetryIspInfo)) + "&dl=" + encodeURIComponent(dlStatus) + "&ul=" + encodeURIComponent(ulStatus) + "&ping=" + encodeURIComponent(pingStatus) + "&jitter=" + encodeURIComponent(jitterStatus) + "&log=" + encodeURIComponent(settings.telemetry_level > 1 ? log : "");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(postData);
  }
}