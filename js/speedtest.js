(function ($) {
  function url_sep(url) {
    return url.match(/\?/) ? "&" : "?";
  }
  function Speedtest() {
    this._state = undefined;
    this._settings = {
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
      forceIE11Workaround: false //when set to true, it will foce the IE11 upload test on all browsers. Debug only
    };
    this._value = {
      download: "",
      upload: "",
      ping: "",
      jitter: "",
      client_ip: "",
      isp_info: ""
    };
    this._loaded = {
      download: 0,
      upload: 0
    };
    this._interval = {
      update: null,
      download: null,
      upload: null
    };
    this._test_failed = false;
    this._xhr = [];
  }
  Speedtest.prototype = {
    constructor: Speedtest,
    getState: function() {
      return this._state;
    },
    clear_requests() {
      if (this._xhr) {
        for (var i = 0; i < this._xhr.length; i++) {
          try {
            this._xhr[i].onprogress = null;
            this._xhr[i].onload = null;
            this._xhr[i].onerror = null;
          } catch (e) {}
          try {
            this._xhr[i].upload.onprogress = null;
            this._xhr[i].upload.onload = null;
            this._xhr[i].upload.onerror = null;
          } catch (e) {}
          try {
            this._xhr[i].abort();
          } catch (e) {}
          try {
            delete this._xhr[i];
          } catch (e) {}
        }
        this._xhr = [];
      }
    },
    handle_browser: function() {
      const ua = navigator.userAgent;
      if (this._settings.enable_quirks && /Firefox.(\d+\.\d+)/i.test(ua)) {
        settings.ping_allowPerformanceApi = false;
      }
      if (/Edge.(\d+\.\d+)/i.test(ua)) {
        this._settings.xhr_dlMultistream = 3;
      }
      if (/Chrome.(\d+)/i.test(ua) && !!self.fetch) {
        this._settings.xhr_dlMultistream = 5;
      }
      if (/Edge.(\d+\.\d+)/i.test(ua)) {
        this._settings.forceIE11Workaround = true;
      }
      if (/PlayStation 4.(\d+\.\d+)/i.test(ua)) {
        this._settings.forceIE11Workaround = true;
      }
      if (/Chrome.(\d+)/i.test(ua) && /Android|iPhone|iPad|iPod|Windows Phone/i.test(ua)) {
        this._settings.xhr_ul_blob_megabytes = 4;
      }
      if (/^((?!chrome|android|crios|fxios).)*safari/i.test(ua)) {
        this._settings.forceIE11Workaround = true;
      }
      this._settings.test_order = this._settings.test_order.toUpperCase();
    },
    ping_test: function() {
      var prevT = null;
      var ping = 0.0;
      var jitter = 0.0;
      var i = 0;
      var prevInstspd = 0;
      var do_ping = function(speedtest) {
        prevT = new Date().getTime();
        speedtest._xhr[0] = new XMLHttpRequest();
        speedtest._xhr[0].onload = function() {
          if (i === 0) {
            prevT = new Date().getTime();
          } else {
            var instspd = new Date().getTime() - prevT;
            if (speedtest._settings.ping_allowPerformanceApi) {
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
          speedtest._value.ping = ping.toFixed(2);
          speedtest._value.jitter = jitter.toFixed(2);
          i++;
          if (i < speedtest._settings.count_ping) {
            do_ping(speedtest);
          } else {
            speedtest.end();
          }
        }.bind(this);
        speedtest._xhr[0].onerror = function() {
          if (speedtest._settings.xhr_ignoreErrors === 0) {
            speedtest._value.ping = "Fail";
            speedtest._value.jitter = "Fail";
            speedtest.end();
          }
          if (speedtest._settings.xhr_ignoreErrors === 1) {
            do_ping(speedtest);
          }
          if (speedtest._settings.xhr_ignoreErrors === 2) {
            i++;
            if (i < speedtest._settings.count_ping) {
              do_ping(speedtest);
            } else {
              speedtest.end();
            }
          }
        }.bind(this);
        speedtest._xhr[0].open("GET", speedtest._settings.url_ping + url_sep(speedtest._settings.url_ping) + (speedtest._settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
        speedtest._xhr[0].send();
      }.bind(this);
      do_ping(this);
    },
    upload_test: function() {
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
      for (var i = 0; i < this._settings.xhr_ul_blob_megabytes; i++) {
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
      const testFunction = function(speedtest) {
        var startT = new Date().getTime();
        var bonusT = 0;
        var graceTimeDone = false;
        speedtest._xhr = [];
        var testStream = function(i, delay) {
          setTimeout(function() {
            var prevLoaded = 0;
            var x = new XMLHttpRequest();
            speedtest._xhr[i] = x;
            var ie11workaround;
            if (speedtest._settings.forceIE11Workaround) {
              ie11workaround = true;
            } else {
              try {
                speedtest._xhr[i].upload.onprogress;
                ie11workaround = false;
              } catch (e) {
                ie11workaround = true;
              }
            }
            if (ie11workaround) {
              speedtest._xhr[i].onload = speedtest._xhr[i].onerror = function() {
                speedtest._loaded.upload += reqsmall.size;
                testStream(i, 0);
              };
              speedtest._xhr[i].open("POST", speedtest._settings.url_ul + url_sep(speedtest._settings.url_ul) + (speedtest._settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
              try {
                speedtest._xhr[i].setRequestHeader("Content-Encoding", "identity");
              } catch (e) {}
              speedtest._xhr[i].send(reqsmall);
            } else {
              speedtest._xhr[i].upload.onprogress = function(event) {
                var loadDiff = event.loaded <= 0 ? 0 : event.loaded - prevLoaded;
                if (isNaN(loadDiff) || !isFinite(loadDiff) || loadDiff < 0) {
                  return;
                }
                speedtest._loaded.upload += loadDiff;
                prevLoaded = event.loaded;
              }.bind(this);
              speedtest._xhr[i].upload.onload = function() {
                testStream(i, 0);
              }.bind(this);
              speedtest._xhr[i].upload.onerror = function() {
                if (speedtest._settings.xhr_ignoreErrors === 0) {
                  speedtest._test_failed = true;
                }
                try {
                  speedtest._xhr[i].abort();
                } catch (e) {}
                delete speedtest._xhr[i];
                if (speedtest._settings.xhr_ignoreErrors === 1) {
                  testStream(i, 0);
                }
              }.bind(this);
              speedtest._xhr[i].open("POST", speedtest._settings.url_ul + url_sep(speedtest._settings.url_ul) + (speedtest._settings.mpot ? "cors=true&" : "") + "r=" + Math.random(), true);
              try {
                speedtest._xhr[i].setRequestHeader("Content-Encoding", "identity");
              } catch (e) {}
              speedtest._xhr[i].send(req);
            }
          }.bind(this), delay);
        }.bind(this);
        for (var i = 0; i < speedtest._settings.xhr_ulMultistream; i++) {
          testStream(i, speedtest._settings.xhr_multistreamDelay * i);
        }
        speedtest._interval.upload = setInterval(function() {
          var t = new Date().getTime() - startT;
          if (t < 200) {
            return;
          }
          if (!graceTimeDone) {
            if (t > 1000 * speedtest._settings.time_ulGraceTime) {
              if (speedtest._loaded.upload > 0) {
                startT = new Date().getTime();
                bonusT = 0;
                speedtest._loaded.upload = 0.0;
              }
              graceTimeDone = true;
            }
          } else {
            var speed = speedtest._loaded.upload / (t / 1000.0);
            if (speedtest._settings.time_auto) {
              var bonus = (5.0 * speed) / 100000;
              bonusT += bonus > 400 ? 400 : bonus;
            }
            speedtest._value.upload = ((speed * 8 * speedtest._settings.overheadCompensationFactor) / (speedtest._settings.useMebibits ? 1048576 : 1000000)).toFixed(2);
            if ((t + bonusT) / 1000.0 > speedtest._settings.time_ul_max || speedtest._test_failed) {
              if (isNaN(speedtest._value.upload)) {
                speedtest._value.upload = "Fail";
              }
              speedtest.clear_requests();
              clearInterval(speedtest._interval.upload);
              speedtest.ping_test();
            }
          }
        }.bind(this), 200);
      }.bind(this);
      if (this._settings.mpot) {
        this.xhr = [];
        this.xhr[0] = new XMLHttpRequest();
        this.xhr[0].onload = this.xhr[0].onerror = function() {
          testFunction();
        }.bind(this);
        this.xhr[0].open("POST", settings.url_ul);
        this.xhr[0].send();
      } else {
        testFunction(this);
      }
    },
    test_dl_stream: function(i, delay) {
      setTimeout((function(speedtest) {
        return function() {
          var prevLoaded = 0;
          var x = new XMLHttpRequest();
          speedtest._xhr[i] = x;
          speedtest._xhr[i].onprogress = function(event) {
            if (speedtest._state === "ended") {
              try {
                x.abort();
              } catch (e) {}
            }
            var loadDiff = event.loaded <= 0 ? 0 : event.loaded - prevLoaded;
            if (isNaN(loadDiff) || !isFinite(loadDiff) || loadDiff < 0) {
              return;
            }
            speedtest._loaded.download += loadDiff;
            prevLoaded = event.loaded;
          }.bind(this);
          speedtest._xhr[i].onload = function() {
            try {
              speedtest._xhr[i].abort();
            } catch (e) {}
            speedtest.test_dl_stream(i, 0);
          }.bind(this);
          speedtest._xhr[i].onerror = function() {
            if (speedtest._settings.xhr_ignoreErrors === 0) {
              speedtest._test_failed = true;
            }
            try {
              speedtest._xhr[i].abort();
            } catch (e) {}
            delete speedtest._xhr[i];
            if (speedtest._settings.xhr_ignoreErrors === 1) {
              speedtest.test_dl_stream(i, 0);
            }
          }.bind(this);
            try {
              if (speedtest._settings.xhr_dlUseBlob) {
                speedtest._xhr[i].responseType = "blob";
              } else {
                speedtest._xhr[i].responseType = "arraybuffer";
              }
            } catch (e) {}
            speedtest._xhr[i].open("GET", speedtest._settings.url_dl + url_sep(speedtest._settings.url_dl) + (speedtest._settings.mpot ? "cors=true&" : "") + "r=" + Math.random() + "&ckSize=" + speedtest._settings.garbagePhp_chunkSize, true);
            speedtest._xhr[i].send();
        }})(this), 1 + delay);
    },
    download_test: function() {
      for (var i = 0; i < this._settings.xhr_dlMultistream; i++) {
        this.test_dl_stream(i, this._settings.xhr_multistreamDelay * i);
      }
      this._interval.download = setInterval((function(speedtest) {
        var bonusT = 0;
        var startT = new Date().getTime();
        var graceTimeDone = false;
        return function() {
          var t = new Date().getTime() - startT;
          if (graceTimeDone) {
            speedtest._value.download = (t + bonusT) / (speedtest._settings.time_dl_max * 1000);
          }
          if (t < 200) {
            return;
          }
          if (!graceTimeDone) {
            if (t > 1000 * speedtest._settings.time_dlGraceTime) {
              if (speedtest._loaded.download > 0) {
                startT = new Date().getTime();
                bonusT = 0;
                speedtest._loaded.download = 0.0;
              }
              graceTimeDone = true;
            }
          } else {
            var speed = speedtest._loaded.download / (t / 1000.0);
            if (speedtest._settings.time_auto) {
              var bonus = (5.0 * speed) / 100000;
              bonusT += bonus > 400 ? 400 : bonus;
            }
            speedtest._value.download = ((speed * 8 * speedtest._settings.overheadCompensationFactor) / (speedtest._settings.useMebibits ? 1048576 : 1000000)).toFixed(2);
            if ((t + bonusT) / 1000.0 > speedtest._settings.time_dl_max || speedtest._test_failed) {
              if (speedtest._test_failed || isNaN(speedtest._value.download)) {
                speedtest._value.download = "Fail";
              }
              speedtest.clear_requests();
              clearInterval(speedtest._interval.download);
              speedtest.upload_test();
            }
          }
        }
      })(this), 200);
    },
    get_ip: async function() {
      const url = this._settings.url_getIp + url_sep(this._settings.url_getIp) + (this._settings.mpot ? "cors=true&" : "") + (this._settings.getIp_ispInfo ? "isp=true" + (this._settings.getIp_ispInfo_distance ? "&distance=" + this._settings.getIp_ispInfo_distance + "&" : "&") : "&") + "r=" + Math.random();
      const response = await fetch(url);
      const data = await response.json();
      this._value.client_ip = data.processedString;
      this._value.isp_info = data.rawIspInfo;
    },
    update: function () {
      if (this._state === undefined || this._state === "aborted") {
        $("#result").css("display", "none");
        $("#ip").text("");
        $("#download").text("");
        $("#upload").text("");
        $("#ping").text("");
        $("#jitter").text("");
      } else {
        $("#ip").text(this._value.client_ip);
        $("#download").text((this._state === undefined || !this._value.download) ? "..." : this._value.download);
        $("#upload").text((this._state === undefined || !this._value.upload) ? "..." : this._value.upload);
        $("#ping").text(this._value.ping);
        $("#jitter").text(this._value.jitter);
      }
    },
    start: function() {
      if (this._state === "running") {
        return;
      } else {
        $("#launch_test").attr("class", "running");
        this._state = "running";
        this._interval.update = setInterval((function(speedtest) {
          return function() {
            speedtest.update();
          }
        })(this), 200);
        this.handle_browser();
        this.get_ip();
        this.download_test();
      }
    },
    clear: function() {
      this.clear_requests();
      clearInterval(this._interval.update);
      clearInterval(this._interval.download);
      clearInterval(this._interval.upload);
      this._test_failed = false;
      this._loaded.download = 0;
      this._loaded.upload = 0;
      this._value.client_ip = "";
      this._value.isp_info = "";
      this._value.download = "";
      this._value.upload = "";
      this._value.ping = "";
      this._value.jitter = "";
    },
    abort: function() {
      this._state = "aborted";
      this.end();
    },
    end: function() {
      this.update();
      $("#launch_test").attr("class", "");
      if (this._state === "aborted") {
        this._state = undefined;
        this.clear();
        $("#result").css("display", "none");
        return;
      } else {
        const speedtest_result = {
          "ip": $("#ip").text(),
          "download": parseFloat($("#download").text()),
          "upload": parseFloat($("#upload").text()),
          "ping": parseFloat($("#ping").text())
        };
        if (speedtest_result["download"] >= 25 && speedtest_result["upload"] >= 25) {
          $("#connection_quality").text("de bonne qualité");
          $("#connection_quality").css("color", "green");
        } else if (speedtest_result["download"] >= 10 && speedtest_result["upload"] >= 10) {
          $("#connection_quality").text("moyenne");
          $("#connection_quality").css("color", "#F7D260");
        } else {
          $("#connection_quality").text("de mauvaise qualité");
          $("#connection_quality").css("color", "red");
        }
        $("#offer_link").attr("href", "https://google.com/");
        $("#result").css("display", "block");
        this._state = "ended";
        this.clear();
      }
    }
  };
  $(document).ready(function () {
    const speedtest = new Speedtest();
    $("#launch_test").on("click", function() {
      if (speedtest.getState() === undefined || speedtest.getState() === "ended") {
        $("#result").css("display", "none");
        speedtest.start();
      } else {
        speedtest.abort();
      }
    });
  });
})(jQuery);