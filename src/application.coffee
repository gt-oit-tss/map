# Application Setup
# =================

# Create a class to act as a namespace for the application.
class Application
  constructor: ->
    @basepath = ''
    @locationsQueue = ["lwc", "lec"]
    @refreshTimer = null
    @refreshInterval = 5 * 1000
    @cycleTimer = null

  refresh: =>
    @map.computers.fetch()

  cycleLocations: =>
    currentLocation = @map.get 'location'
    nextLocation = @locationsQueue.shift()
    while nextLocation == currentLocation
      @locationsQueue.push nextLocation
      nextLocation = @locationsQueue.shift()
    @map.set
      location: nextLocation
    @locationsQueue.push nextLocation

  startAutoRefresh: ->
    @refreshTimer = setInterval @.refresh, @refreshInterval

  stopAutoRefresh: ->
    clearInterval @refreshTimer
    @refreshTimer = null

  startLocationCycling: ->
    if @refreshTimer
      @.stopAutoRefresh()
    @cycleTimer = setInterval @.cycleLocations, @refreshInterval
    @.startAutoRefresh()

  stopLocationCycling: ->
    clearInterval @cycleTimer
    @cycleTimer = null

CmsApplication = window.CmsApplication = new Application

# Models and Collections
# ======================

# Computer Model
# --------------

class Computer extends Backbone.Model
  defaults:
    "status": "unavailable"

# Computer Collection
# -------------------

class ComputerList extends Backbone.Collection
  model: Computer
  url: "#{CmsApplication.basepath}/api/v2/computers"

# Map Model
# ---------

class Map extends Backbone.Model

  # When a **Map** is created, give it an empty **ComputerList** collection,
  # and update the collection's `url` attribute when the map's `location`
  # attribute is updated.
  initialize: ->
    @computers = new ComputerList()
    @.updateComputerUrl @get 'location'
    @.bind 'change:location', @.updateComputerUrl

  updateComputerUrl: =>
    @computers.url = "#{CmsApplication.basepath}/api/v2/maps/#{@get 'location'}/computers/"
    $("#display-map").data('location', @get 'location')

# Views
# =====

# Map View
# --------

class MapView extends Backbone.View
  # Store the template for rendering computers, and render the map any time
  # computer data change.
  initialize: ->
    @computerTemplate = _.template $("#computer-template").html()
    CmsApplication.map.computers.bind 'all', @.render

  render: =>
    @.$("dl")
      .removeClass()
      .addClass "map_#{CmsApplication.map.get 'location'}"
    computerHtml = _.map CmsApplication.map.computers.toJSON(), @.generateMarkup
    @.$("dl").html computerHtml.join ''
    @

  generateMarkup: (data) =>
    @.computerTemplate data

# Header View
# -----------

class HeaderView extends Backbone.View
  initialize: ->
    @cycleMapsCheckboxTemplate = '''
    <label for="cycle-maps">
      <input type="checkbox" id="cycle-maps" name="cycle-maps" />
      Cycle Maps |
    </label>
    '''
    @.$('.nav li:first-child').replaceWith $("<li/>",
      html: $(@cycleMapsCheckboxTemplate)
    )
    @locationDescriptions =
      "lwc": "Library West Commons"
      "lec": "Library East Commons"
    CmsApplication.map.computers.bind 'all', @.render

  events:
    "click .nav a": "changeMapLocation"
    "change #cycle-maps": "toggleMapCycling"

  render: =>
    location = CmsApplication.map.get 'location'
    now = new Date()

    @.$(".page-title").text(@locationDescriptions[location])
    @.$(".subtitle span").text(now.toString())
    @

  changeMapLocation: (event) ->
    event.preventDefault()
    location = $(event.target).data 'mapLink'
    CmsApplication.map.set
      location: location
    CmsApplication.map.computers.fetch()
    false

  toggleMapCycling: (event) ->
    if @.$("#cycle-maps:checked").val()
      CmsApplication.startLocationCycling()
    else
      CmsApplication.stopLocationCycling()



# Main Application
# ================

# When the DOM is loaded...
$ ->
  # Set the basepath of the app using an HTML5 data attribute.
  CmsApplication.basepath = $("#content").data('basepath');

  # Create the app components.
  CmsApplication.map = new Map
    "location": $("#display-map").data('location')
  CmsApplication.mapView = new MapView
    el: $("#display-map")
  CmsApplication.headerView = new HeaderView
    el: $("#header")

  # Start refreshing the map automatically.
  CmsApplication.startAutoRefresh()

  # Enable map cycling if viewing the app root.
  if "#{CmsApplication.basepath}/" == window.location.pathname
    $("#cycle-maps").click()
