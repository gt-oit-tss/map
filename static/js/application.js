(function() {
  var Application, CmsApplication, Computer, ComputerList, HeaderView, Map, MapView;
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; }, __hasProp = Object.prototype.hasOwnProperty, __extends = function(child, parent) {
    for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; }
    function ctor() { this.constructor = child; }
    ctor.prototype = parent.prototype;
    child.prototype = new ctor;
    child.__super__ = parent.prototype;
    return child;
  };
  Application = (function() {
    function Application() {
      this.cycleLocations = __bind(this.cycleLocations, this);;
      this.refresh = __bind(this.refresh, this);;      this.basepath = '';
      this.locationsQueue = ["lwc", "lec"];
      this.refreshTimer = null;
      this.refreshInterval = 5 * 1000;
      this.cycleTimer = null;
    }
    Application.prototype.refresh = function() {
      return this.map.computers.fetch();
    };
    Application.prototype.cycleLocations = function() {
      var currentLocation, nextLocation;
      currentLocation = this.map.get('location');
      nextLocation = this.locationsQueue.shift();
      while (nextLocation === currentLocation) {
        this.locationsQueue.push(nextLocation);
        nextLocation = this.locationsQueue.shift();
      }
      this.map.set({
        location: nextLocation
      });
      return this.locationsQueue.push(nextLocation);
    };
    Application.prototype.startAutoRefresh = function() {
      return this.refreshTimer = setInterval(this.refresh, this.refreshInterval);
    };
    Application.prototype.stopAutoRefresh = function() {
      clearInterval(this.refreshTimer);
      return this.refreshTimer = null;
    };
    Application.prototype.startLocationCycling = function() {
      if (this.refreshTimer) {
        this.stopAutoRefresh();
      }
      this.cycleTimer = setInterval(this.cycleLocations, this.refreshInterval);
      return this.startAutoRefresh();
    };
    Application.prototype.stopLocationCycling = function() {
      clearInterval(this.cycleTimer);
      return this.cycleTimer = null;
    };
    return Application;
  })();
  CmsApplication = window.CmsApplication = new Application;
  Computer = (function() {
    function Computer() {
      Computer.__super__.constructor.apply(this, arguments);
    }
    __extends(Computer, Backbone.Model);
    Computer.prototype.defaults = {
      "status": "unavailable"
    };
    return Computer;
  })();
  ComputerList = (function() {
    function ComputerList() {
      ComputerList.__super__.constructor.apply(this, arguments);
    }
    __extends(ComputerList, Backbone.Collection);
    ComputerList.prototype.model = Computer;
    ComputerList.prototype.url = "" + CmsApplication.basepath + "/api/v2/computers";
    return ComputerList;
  })();
  Map = (function() {
    function Map() {
      this.updateComputerUrl = __bind(this.updateComputerUrl, this);;      Map.__super__.constructor.apply(this, arguments);
    }
    __extends(Map, Backbone.Model);
    Map.prototype.initialize = function() {
      this.computers = new ComputerList();
      this.updateComputerUrl(this.get('location'));
      return this.bind('change:location', this.updateComputerUrl);
    };
    Map.prototype.updateComputerUrl = function() {
      this.computers.url = "" + CmsApplication.basepath + "/api/v2/maps/" + (this.get('location')) + "/computers/";
      return $("#display-map").data('location', this.get('location'));
    };
    return Map;
  })();
  MapView = (function() {
    function MapView() {
      this.generateMarkup = __bind(this.generateMarkup, this);;
      this.render = __bind(this.render, this);;      MapView.__super__.constructor.apply(this, arguments);
    }
    __extends(MapView, Backbone.View);
    MapView.prototype.initialize = function() {
      this.computerTemplate = _.template($("#computer-template").html());
      return CmsApplication.map.computers.bind('all', this.render);
    };
    MapView.prototype.render = function() {
      var computerHtml;
      this.$("dl").removeClass().addClass("map_" + (CmsApplication.map.get('location')));
      computerHtml = _.map(CmsApplication.map.computers.toJSON(), this.generateMarkup);
      this.$("dl").html(computerHtml.join(''));
      return this;
    };
    MapView.prototype.generateMarkup = function(data) {
      return this.computerTemplate(data);
    };
    return MapView;
  })();
  HeaderView = (function() {
    function HeaderView() {
      this.render = __bind(this.render, this);;      HeaderView.__super__.constructor.apply(this, arguments);
    }
    __extends(HeaderView, Backbone.View);
    HeaderView.prototype.initialize = function() {
      this.cycleMapsCheckboxTemplate = '<label for="cycle-maps">\n  <input type="checkbox" id="cycle-maps" name="cycle-maps" />\n  Cycle Maps |\n</label>';
      this.$('.nav li:first-child').replaceWith($("<li/>", {
        html: $(this.cycleMapsCheckboxTemplate)
      }));
      this.locationDescriptions = {
        "lwc": "Library West Commons",
        "lec": "Library East Commons"
      };
      return CmsApplication.map.computers.bind('all', this.render);
    };
    HeaderView.prototype.events = {
      "click .nav a": "changeMapLocation",
      "change #cycle-maps": "toggleMapCycling"
    };
    HeaderView.prototype.render = function() {
      var location, now;
      location = CmsApplication.map.get('location');
      now = new Date();
      this.$(".page-title").text(this.locationDescriptions[location]);
      this.$(".subtitle span").text(now.toString());
      return this;
    };
    HeaderView.prototype.changeMapLocation = function(event) {
      var location;
      event.preventDefault();
      location = $(event.target).data('mapLink');
      CmsApplication.map.set({
        location: location
      });
      CmsApplication.map.computers.fetch();
      return false;
    };
    HeaderView.prototype.toggleMapCycling = function(event) {
      if (this.$("#cycle-maps:checked").val()) {
        return CmsApplication.startLocationCycling();
      } else {
        return CmsApplication.stopLocationCycling();
      }
    };
    return HeaderView;
  })();
  $(function() {
    CmsApplication.basepath = $("#content").data('basepath');
    CmsApplication.map = new Map({
      "location": $("#display-map").data('location')
    });
    CmsApplication.mapView = new MapView({
      el: $("#display-map")
    });
    CmsApplication.headerView = new HeaderView({
      el: $("#header")
    });
    CmsApplication.startAutoRefresh();
    if (("" + CmsApplication.basepath + "/") === window.location.pathname) {
      return $("#cycle-maps").click();
    }
  });
}).call(this);
