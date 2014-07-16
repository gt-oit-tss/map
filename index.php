<?php
/**
 * @package LibraryCMS
 * @author Leroy Campbell
 * @copyright 2008-2011 - Office of Information Technology (Georgia Tech)
 */ 


/**
 * The **Cluster Monitoring Suite** for the Georgia Tech Library is a web
 * application that shows which computers are available in the library's
 * computer labs. The application targets PHP 5.2.6 and higher.
 */
require 'Slim/Slim.php';

/**
 * Configuration
 * =============
 */

/**
 * Initialize Slim before adding routes, callbacks, etc.
 */
Slim::init(array(
  'log.enable' => false
));


/**
 * Callbacks
 * =========
 */
 
/**
 * Before each request, create a PDO database handler.
 */
Slim::before('open_db_handler');
function open_db_handler() {
  try {
    $dbh = new PDO('sqlite:' . dirname(__FILE__) . '/db/app.db');
    Slim::config('db_handler', $dbh);
  } catch (PDOException $e) {
    Slim_Log::fatal('PDO error ' . $e);
    Slim::halt(500, 'Sorry. Something went wrong.');
  }
}

Slim::before('get_base_url');
function get_base_url() {
  $base_url = Slim::request()->getRootUri();
  Slim::view()->appendData(array('base_url' => $base_url));
}

/**
 * After each request, close the database handler.
 */
// Slim::after('close_db_handler');
// function close_db_handler() {
//   $dbh = null;
// }

Slim::after('disable_ajax_caching');
function disable_ajax_caching() {
  Slim::response()->header('Cache-Control', 'no-cache,no-store');
  Slim::response()->header('Expires', '-1');
}


/**
 * Helper Functions
 * ================
 */
 
/**
 * Given an array, set the value of `available`, `in-use`, or `unavailable` to "0"
 * if the key doesn't exist.
 */
function set_default_status_counts($data) {
  $new_data = array();
  
  if (array_key_exists('available', $data)) {
    $new_data['available'] = $data['available'];
  } else {
    $new_data['available'] = '0';
  }
  
  if (array_key_exists('in-use', $data)) {
    $new_data['in-use'] = $data['in-use'];
  } else {
    $new_data['in-use'] = '0';
  }
  
  if (array_key_exists('unavailable', $data)) {
    $new_data['unavailable'] = $data['unavailable'];
  } else {
    $new_data['unavailable'] = '0';
  }
  
  return $new_data;
}

/**
 * Given a PDO query statement, generate a log message if necessary.
 */
function handle_query_errors($pdo_statement) {
  $error_info = $pdo_statement->errorInfo();
  
  if (preg_match("/^00/i", strval($error_info[0]))) {
    return;
    // Slim_Log::info('Query executed successfully.');
  } else if (preg_match("/^(?:01|IM)/i", strval($error_info[0]))) {
    Slim_Log::warn('Query successful, with warning: ' . $error_info[2]);
  } else {
    Slim_Log::error('Query failed: ' . $error_info[2]);
    Slim::halt(500, 'Something went wrong.');
  }
}

/**
 * Routes
 * ======
 */

/**
 * The old version of the app can be accessed at `/v1`.
 */
Slim::get('/v1/', 'legacy_app');
function legacy_app() {
  Slim::redirect('/v1/index.php');
}

/**
 * Redirect legacy client calls to the new URLs.
 * The update function is defined with the rest of the new API calls.
 */
Slim::post('/api.php', 'update_status_by_name');

/**
 * For compatibility with the legacy API, if `/?q=count` is requested, an XML document is returned.
 * Otherwise, the client is redirected to the _Library West Commons_ map.
 */
Slim::get('/', 'homepage');
function homepage() {
  if (Slim::request()->get('q') == 'count') {
    $dbh = Slim::config('db_handler');
    $query = $dbh->prepare("SELECT location, status, COUNT(*) as count FROM computers GROUP BY location, status");
    $query->execute();
    
    handle_query_errors($query);
    
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $locations[$row['location']][$row['status']] = $row['count'];
    }
    
    Slim::view()->appendData(array(
      'locations' => array_map('set_default_status_counts', $locations)
    ));
    
    // Slim::response()->header('Content-Type', 'text/plain'); // for testing
    Slim::response()->header('Content-Type', 'Content-type: application/xml; charset="utf-8"');
    Slim::render('count_xml.php');
  } else {
    $dbh = Slim::config('db_handler');
    $query = $dbh->prepare("SELECT * FROM computers WHERE location = 'lwc'");
    $query->execute();
    
    handle_query_errors($query);
    
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $computers[] = $row;
    }
    
    Slim::view()->appendData(array(
      'title' => 'Library West Commons',
      'map' => 'lwc',
      'computers' => $computers,
      'cycle_maps' => true
    ));
    
    if (Slim::request()->get('large')) {
      Slim::render('map_large.php');
    } else {
      Slim::render('map.php');
    }
  }
}

/**
 * Show the status of the computers in the selected cluster.
 */
Slim::get('/maps/:name/', 'lwc_map');
function lwc_map($name) {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT * FROM computers WHERE location = :loc");
  $query->bindValue(':loc', strtolower($name));
  $query->execute();
  
  handle_query_errors($query);
  
  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $computers[] = $row;
  }
  
  Slim::view()->appendData(array(
    'title' => $name == 'lec' ? 'Library East Commons' : 'Library West Commons',
    'map' => $name,
    'computers' => $computers
  ));
  
  if (Slim::request()->get('large')) {
    Slim::render('map_large.php');
  } else {
    Slim::render('map.php');
  }
}

/**
 * Display the computer administration page if the user is authorized.
 */
//Slim::get('/admin/', 'admin_home');
function admin_home() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT * FROM computers");
  $query->execute();
  
  handle_query_errors($query);
  
  while ($row = $query->fetch()) {
    $computers[] = $row;
  }
  
  Slim::view()->appendData(array(
    'computers' => $computers
  ));
  
  Slim::render('admin.html');
}

/**
 * API
 * ---
 */
 
/**
 * Return a JSON string of all computers in a given location.
 */
Slim::get('/api/v2/maps/:location/computers/', 'all_computers_for_location_json');
function all_computers_for_location_json($location) {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT * FROM computers WHERE location = :location");
  $query->bindValue(':location', $location);
  $query->execute();
  
  handle_query_errors($query);  

  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $computers[] = $row;
  }
  
  Slim::response()->header('Content-Type', 'text/plain');
  echo json_encode($computers);
}

/**
 * Return a JSON string of all computers.
 */
Slim::get('/api/v2/computers/', 'all_computers_json');
function all_computers_json() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT * FROM computers");
  $query->execute();
  
  handle_query_errors($query);  

  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $computers[] = $row;
  }
  
  Slim::response()->header('Content-Type', 'text/plain');
  echo json_encode($computers);
}

/**
 * Return a JSON string of status counts by location.
 */
Slim::get('/api/v2/computers/status', 'get_statuses_json');
function get_statuses_json() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT location, status, COUNT(*) as count FROM computers GROUP BY location, status");
  $query->execute();
  
  handle_query_errors($query);
  
  while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $locations[$row['location']][$row['status']] = $row['count'];
  }
  
  Slim::response()->header('Content-Type', 'text/plain');
  echo json_encode($locations);
}

/**
 * Update a single computer's status given its name.
 */
Slim::post('/api/v2/computers/status/update', 'update_status_by_name');
function update_status_by_name() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("UPDATE computers SET status = :status, last_updated = CURRENT_TIMESTAMP WHERE name = :name");
  $query->bindValue(':status', Slim::request()->params('status'));
  $query->bindValue(':name', Slim::request()->params('name'));
  
  Slim::response()->header('Content-Type', 'text/plain');
  
  if (Slim::request()->params('name') && Slim::request()->params('status')) {
    $name = Slim::request()->params('name');
    $status = Slim::request()->params('status');
    $query->execute();
    
    handle_query_errors($query);
    
    Slim_Log::info("$name is now $status");
    echo json_encode(array("message" => "okay"));
  } else {
    Slim::error('Computer status update failed.');
    Slim::halt(400, json_encode(array("error" => 'The specified computer cannot be updated.')));
  }
}

/**
 * Return a single computer as JSON. Must appear after other computer routes.
 */
Slim::get('/api/v2/computers/:id', 'get_computer_json');
function get_computer_json($id) {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT * FROM computers WHERE id = :id");
  $query->bindValue(':id', $id, PDO::PARAM_INT);
  $query->execute();
  
  handle_query_errors($query);
  
  $computer = $query->fetch(PDO::FETCH_ASSOC);
  
  Slim::response()->header('Content-Type', 'text/plain');
  echo json_encode($computer);
}

/**
 * Administration
 * --------------
 */
 
/**
 * Run application updates (e.g., database migrations) for current app version.
 */
 
/*
Slim::get('/system/update', 'check_app_update_status');
function check_app_update_status() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT COUNT(*) as count FROM computers WHERE name LIKE 'ATS-%'");
  $query->execute();
  
  handle_query_errors($query);
  
  $row = $query->fetch(PDO::FETCH_ASSOC);
  
  if ($row['count'] > "0") {
    $descriptions[] = "Rename computer database records with TSS prefix";
    
    Slim::view()->appendData(array(
      'updates_available' => true,
      'descriptions' => $descriptions
    ));
  }
  
  Slim::render('update.php');
}

Slim::post('/system/update', 'apply_app_updates');
function apply_app_updates() {
  $dbh = Slim::config('db_handler');
  $query = $dbh->prepare("SELECT name FROM computers");
  $query->execute();
  
  handle_query_errors($query);
  
  while($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $computer = array(
      'old_name' => $row['name'],
      'new_name' => preg_replace('/^ATS-(\w+\d*)/', 'TSS-$1', $row['name'])
    );
    $computers[] = $computer;
  }
  
  $query = $dbh->prepare("UPDATE computers SET name = :newname WHERE name = :oldname");
  
  foreach ($computers as $computer) {
    $query->execute(array(
      ":newname" => $computer['new_name'],
      ":oldname" => $computer['old_name']
    ));
    handle_query_errors($query);
  }
  
  Slim::render('update.php');
}
*/

/**
 * Application
 * ===========
 */

/**
 * Finally, run the application.
 */
Slim::run();

?>
