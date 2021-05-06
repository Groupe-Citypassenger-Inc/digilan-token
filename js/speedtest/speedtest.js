(function ($) {
  function Speedtest() {
    this._state = undefined;
    this._settings = {};
  }
  Speedtest.prototype = {
    constructor: Speedtest,
    getState: function() {
      return this._state;
    },
	  start: function() {
	    if (this._state === "running") {
        return;
      } else {
        init_user_interface();
        $("#startStopBtn").attr("class", "running");
        this.worker = new Worker("/digilanToken/wp-content/plugins/digilan-token/js/speedtest/speedtest_worker.js?r=" + Math.random());
        this.worker.onmessage = function(e) {
          if (e.data === this._prevData) {
            return;
          } else {
            this._prevData = e.data;
          }
          var data = JSON.parse(e.data);
          this.onupdate(data);
          if (data.testState < 4) {
            return;
          } else {
            clearInterval(this.updater);
            this._state = "ended";
            this.onend(data.testState == 5);
          }
        }.bind(this);
        this.updater = setInterval(function() {
          this.worker.postMessage("status");
        }.bind(this), 200);
          this._state = "running";
          this.worker.postMessage("start " + JSON.stringify(this._settings));
      }
	  },
		abort: function() {
      this.worker.postMessage("abort");
	  }
  };
  var speed = new Speedtest();
  speed.onupdate = function(data) {
		$("#ip").text(data.clientIp);
		$("#dlText").text((data.testState == 1 && data.dlStatus == 0) ? "..." : data.dlStatus);
		$("#ulText").text((data.testState == 3 && data.ulStatus == 0) ? "..." : data.ulStatus);
		$("#pingText").text(data.pingStatus);
		$("#jitText").text(data.jitterStatus);
  }
  speed.onend = function(aborted) {
		$("#startStopBtn").attr("class", "");
		if (aborted) {
			return;
		} else {
      const speedtest_result = {
        "download": parseFloat($("#dlText").text()),
        "upload": parseFloat($("#ulText").text()),
        "ip": $("#ip").text(),
        "mail": $("#mail").text(),
        "ping": $("#pingText").text()
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
    }
  }
  function init_user_interface() {
	  $("#result").css("display", "none");
	  $("#dlText").text("");
	  $("#ulText").text("");
	  $("#pingText").text("");
	  $("#jitText").text("");
		$("#ip").text("");
  }
	$(document).ready(function () {
    $("#startStopBtn").on("click", function() {
      if (speed.getState() === undefined || speed.getState() === "ended") {
        speed.start();
      } else {
        speed.abort();
      }
    });
	  init_user_interface();
	});
})(jQuery);