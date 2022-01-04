<?php
require_once 'API.class.php';
require_once 'MysqliDb.php';
require_once 'JWT.php';
require_once 'ExpiredException.php';
require_once 'BeforeValidException.php';
require_once 'SignatureInvalidException.php';
use Firebase\JWT\JWT;

define("SECRET_KEY", "Cw2Ii8vhC]Vi<m,");
define("ALGO", "HS512");
define("DOMAIN", "fluxshopmanager.com");
define("SESSION_TIME_SECONDS",1800);

class Flux extends API
{
    protected $user;
    protected $db;
    protected $token;
    protected $refreshedToken;

    public function __construct($request, $origin) {
        parent::__construct($request);
        $this->db = new MysqliDb ('localhost', 'fluxie', 'VLByuQFdt2373', 'fluxshopmanager_com');

        $headers = apache_request_headers();
        if(array_key_exists("Authorization", $headers)) {
        	try {
        		$t = substr($headers['Authorization'],7);
        		$token = JWT::decode($t, SECRET_KEY, array(ALGO));
        		$this->user = $token->data;
        		$this->db->where('tokenid', $token->jti);
        		$this->db->where('ipaddress', $_SERVER['REMOTE_ADDR']);
        		$usersession = array("lastused" => $this->db->now());
        		$this->db->update('usersession',$usersession);
        		$this->token = $token->jti;
			} catch(\Firebase\JWT\ExpiredException $e) {
				$tks = explode('.', substr($headers['Authorization'],7));
				$payload = JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
				
				$tknCount = $this->db->rawQueryValue('SELECT count(*) FROM `usersession` where tokenid=? and ipaddress=? and lastused >= DATE_SUB(NOW(), INTERVAL 1 HOUR) limit 1', array($payload->jti,$_SERVER['REMOTE_ADDR']));
				
				if($tknCount < 1) {
					$this->db->where('tokenid', $payload->jti);
					$this->db->where('ipaddress', $_SERVER['REMOTE_ADDR']);
					$this->db->delete('usersession');
					$this->user = null;
				} else {
					$this->db->where('tokenid', $payload->jti);
					$this->db->where('ipaddress', $_SERVER['REMOTE_ADDR']);
					$usersession = array("lastused" => $this->db->now());
					$this->db->update('usersession',$usersession);
					$payload->exp = time() + SESSION_TIME_SECONDS;
					$this->refreshedToken = JWT::encode($payload, SECRET_KEY, ALGO);
					$this->user = $payload->data;
				}
				$this->token = $payload->jti;
			} catch(Exception $e) {
				$this->user = null;
			}
        }
    }

    protected function vehicle() {
    	if($this->user == null) {
    		return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
    	}
    	if($this->method == 'POST') {
    		$vehicle = new stdClass();
    		$body = @file_get_contents('php://input');
    		$body = json_decode($body);
    
    		$vehicle->year = $body->year;
    		$vehicle->make = $body->make;
            $vehicle->model = $body->model;
    		$vehicle->store_id = $body->store;
    			
    		if(isset($body->trim)) {
    			$vehicle->trim = $body->trim;
    		}
    			
    		if(isset($body->mileage)) {
    			$vehicle->mileage = $body->mileage;
    		}
    			
    		if(isset($body->vin)) {
    			$vehicle->vin = $body->vin;
    		}
    			
    		if(isset($body->license)) {
    			$vehicle->license = $body->license;
    		}
    			
    		if(isset($body->active)) {
    			$vehicle->active = $body->active;
    		}
    			
    		if(isset($body->fleetnum)) {
    			$vehicle->fleetnum = trim($body->fleetnum);
    		}

    		// Check if customer is central or external
            $this->db->where('id', $body->customer_id)->where('store_id', $body->store);
            $customer_extrnid = $this->db->getValue('customer', 'extrnid');	
            $vehicle->customer_id = $customer_extrnid ?? $body->customer_id;
    
    		$id = $this->db->insert('vehicle', get_object_vars($vehicle));
            $this->setCustomerCache($vehicle->customer_id, $body->store, time());
    			
    		return $this->handleRefreshToken(array("id"=>$id));
    	} elseif($this->method == 'GET'){
            if(!isset($_GET['store'])) {
                return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
            }
            $storeId = $_GET['store'];
            if(!$this->isUserAuthorizedForStore($storeId)) {
                return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
            }
    		if($this->verb == "byContact") {
    			$beans = $this->db->rawQuery("
						SELECT vehicle.* FROM vehicle
						WHERE vehicle.active=1 AND vehicle.customer_id = ? AND vehicle.store_id = ?
						", array($this->args[0], $storeId) );
                $retArray['data'] = $beans;
    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "search") {
    			$findBy = " active = 1";
    			$argArray = array();
    			if(isset($_GET['vin'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " vin = ? ";
    				$argArray[] = $_GET['vin'];
    			}
    			if(isset($_GET['license'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " license = ? ";
    				$argArray[] = $_GET['license'];
    			}
    
    			$vehicle = null;
    			if($findBy != "") {
    				$vehicle = $this->db->rawQuery("select * from vehicle where $findBy", $argArray );
    			}
                
                $retArray = array('data' => $vehicle);
    			return $this->handleRefreshToken($retArray);
    		} else {
    			$vehicle  = R::findOne('customer', ' id = ? ', array($this->args[0]) );
    			return $this->handleRefreshToken(json_decode($vehicle->__toString()));
    		}
    	} elseif($this->method == 'PUT'){
    		$body = json_decode($this->file);
            if(!isset($body->id)) {
                return "Missing parameter: id";
            }
            
            $this->db->objectBuilder();
            $vehicle = $this->db->rawQueryOne('select * from vehicle where COALESCE(extrnid,id) = ? and store_id= ?', array($body->id, $body->store));
            if($vehicle->customer_id != $body->customer_id) {
                $body->customer_id = $vehicle->customer_id;
            }
                
            $vehicle->year = $body->year;
            $vehicle->make = $body->make;
            $vehicle->model = $body->model;
                
            if(isset($body->trim)) {
                $vehicle->trim = $body->trim;
            } else {
                $vehicle->trim = null;
            }
                
            if(isset($body->mileage)) {
                $vehicle->mileage = $body->mileage;
            } else {
                $vehicle->mileage = null;
            }
                
            if(isset($body->vin)) {
                $vehicle->vin = $body->vin;
            } else {
                $vehicle->vin = null;
            }
                
            if(isset($body->license)) {
                $vehicle->license = $body->license;
            } else {
                $vehicle->license = null;
            }
                
            if(isset($body->active)) {
                $vehicle->active = $body->active;
            }
                
            if(isset($body->fleetnum)) {
                $vehicle->fleetnum = trim($body->fleetnum);
            } else {
                $vehicle->fleetnum = null;
            }
             
            if(isset($body->customer_id)) {
                $vehicle->customer_id = $body->customer_id;
            }
            
            $vehicle->is_updated = true;
            $this->db->where('COALESCE(extrnid, id)', $body->id);
            $id = $this->db->update('vehicle', get_object_vars($vehicle));
            $this->setCustomerCache($vehicle->customer_id, $body->store, time());
            	
    		return $this->handleRefreshToken(array("id"=>$id));
    	} elseif($this->method == 'DELETE'){
    		$vehicle  = R::findOne('vehicle', ' endpoint = ? ', array( $this->verb ) );
    		R::trash($vehicle);
    		return $this->handleRefreshToken(array("success" => true));
    	}
    
    }
    
    protected function order() {
    	if(!($this->verb == "appointment") && $this->user == null) {
    		return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
    	}
    	if($this->method == 'POST') {
            if($this->verb == "appointment") {
                $body = @file_get_contents('php://input');
                $body = json_decode($body);
                $errors = [];
                
                if (!isset($body->full_name) || $body->full_name == null) {
                    array_push($errors, ['full_name' => 'Full name is required.']);
                }

                if (!isset($body->email) || $body->email == null) {
                    array_push($errors, ['email' => 'Email is required.']);
                }

                if (!isset($body->phone) || $body->phone == null) {
                    array_push($errors, ['phone' => 'Phone is required.']);
                }

                if (!isset($body->requested_date) || $body->requested_date == null) {
                    array_push($errors, ['requested_date' => 'Requested date is required.']);
                }

                if (!isset($body->store_id) || $body->store_id == null) {
                    array_push($errors, ['store_id' => 'Store is required.']);
                }

                if (!isset($body->work_requested) || $body->work_requested == null) {
                    array_push($errors, ['work_requested' => 'Work requested requested is required.']);
                }

                if (!empty($errors)) {
                    return ['data' => ['errors' => $errors], 'statuscode' => 400];
                }

                $todayDate = strtotime("today", time());
                $requestedDate = strtotime($body->requested_date);
                if ($requestedDate < $todayDate) {
                    return ['data' => ['error' => 'Requested date should be greater than or equal to ' . date('Y-m-d', $todayDate)], 'statuscode' => 400];
                }

                $store = $this->db->where('id', $body->store_id)->getValue('store', 'id');
                if (!$store) {
                    return ['data' => ['error' => 'Store does not exists.'], 'statuscode' => 404];
                }

                $request = $this->db->rawQueryValue("select id from appointment where email=? and store_id=? and order_id is null limit 1", array($body->email, $body->store_id));
                if ($request) {
                    return ['data' => ['error' => 'Active appointment request already exists.'], 'statuscode' => 404];
                }

                $appointment = new stdClass();
                $appointment->store_id = $body->store_id;
                $appointment->full_name = $body->full_name;
                $appointment->email = $body->email;
                $appointment->phone = $body->phone;
                $appointment->requested_date = $body->requested_date;
                $appointment->work_requested = $body->work_requested;

                $id = $this->db->insert('appointment', get_object_vars($appointment));

                if ($id) {
                    return ['data' => ['success' => 'Appointment was created.'], 'statuscode' => 200];
                }

                return ['data' => ['error' => 'Unable to create Appointment. Bad Request.'], 'statuscode' => 400];
            }

    		$order = new stdClass();
            $body = @file_get_contents('php://input');
            $body = json_decode($body);
            
            $order->created = date('Y-m-d G:i:s');
            $order->updated = date('Y-m-d G:i:s');
            $order->type = $body->tickettype;
            $order->status = $body->ticketstatus;
            $order->promisedtime = $body->promisedtime;

            if(isset($body->startdate) && $body->startdate != null) {
                $order->startdate = $body->startdate;
            } else {
                $order->startdate = null;
            }
            
            if(isset($body->starttime) && $body->starttime != null) {
                $order->starttime = $body->starttime;
            } else {
                $order->starttime = null;
            }
            
            if(isset($body->duration) && $body->duration != null) {
                $order->duration = $body->duration;
            } else {
                $order->duration = null;
            }
            
            if(isset($body->customernotes)) {
                $order->customernotes = $body->customernotes;
            }
            
            if(isset($body->mileage)) {
                $order->mileage = $body->mileage;
            }
            
            if(isset($body->ordertotal)) {
                $order->ordertotal = $body->ordertotal;
            }
                
            if(isset($body->ordertax)) {
                $order->ordertax = $body->ordertax;
            }
                
            if(isset($body->ordermargin)) {
                $order->ordermargin = $body->ordermargin;
            }
            
            $order->contact_id = $body->contact_id;
            if(isset($body->vehicle_id)) {
                $order->vehicle_id = $body->vehicle_id;
            }
            
            $order->optcounter = 1;

            $order->store_id = $body->store;
            $id = $this->db->insert('order', get_object_vars($order));

            if(isset($body->teammember_id) && !empty($body->teammember_id)) {
                foreach($body->teammember_id as $tmid) {
                    $teammember = $this->db->rawQueryOne("select * from teammember where id = ? and store_id = ?", array($tmid, $body->store));
                    $order_teammember = array(
                        'store_id' => $body->store,
                        'teammember_id' => $teammember['id'],
                        'order_id' => $id,
                    );
                    $this->db->insert('order_teammember', $order_teammember);
                }
            }
            
            foreach($body->items as $k => $i) {
                $item = new stdClass();
                $item->order_id = $id;
                $item->itemtype_id = $i->type;
                $item->partnumber = $i->partnumber;
                $item->description = $i->description;
                $item->quantity = $i->quantity;
                $item->retail = $i->retail;
                $item->cost = $i->cost;
                $item->taxcat = $i->taxcat;
                if(isset($i->dotnumber) && $i->dotnumber != "") {
                    $item->dotnumber = $i->dotnumber;
                }
                
                if(isset($i->vendor_id) && $i->vendor_id != "") {
                    $item->vendor_id = $i->vendor_id;
                }
                
                if(isset($i->invoicenumber) && $i->invoicenumber != "") {
                    $item->invoicenumber = $i->invoicenumber;
                }
                
                if(isset($i->tax) && $i->tax != "") {
                    $item->tax = $i->tax;
                }

                $item->store_id = $body->store;
                $this->db->insert('orderitem', get_object_vars($item));
                
                if($i->type == 3 || $i->type == 4) {
                    // Reserve inventory for new items
                    $this->db->objectBuilder();
                    $inv  = $this->db->rawQueryOne( 'select * from inventory where partnumber = ? and store_id = ?', array($i->partnumber, $body->store));
                    if($inv != null) {
                        $inv->reserved += $i->quantity;
                        $inv->is_updated = true;
                        $this->db->where('id', $inv->id);
                        $this->db->update('inventory', get_object_vars($inv));
                    }
                }
            }

            if (isset($body->appointment_id) && $body->appointment_id != null) {
                $this->db->objectBuilder();
                $appointment = $this->db->rawQueryOne("select * from appointment where id = ?", array($body->appointment_id));
                $appointment->order_id = $id;
                $this->db->where('id', $appointment->id);
                $this->db->update('appointment', get_object_vars($appointment));
            }
    
    		return $this->handleRefreshToken(array("id" => 'C'.$id));
    	} elseif($this->method == 'GET'){
    		if(!isset($_GET['store'])) {
    			return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
    		}
    		$storeId = $_GET['store'];
    		
    		if(!$this->isUserAuthorizedForStore($storeId)) {
    		    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
    		}
    		
    		if($this->verb == "open") {
    			
    			/*
    			 * Eastern ........... America/New_York
    			 Central ........... America/Chicago
    			 Mountain .......... America/Denver
    			 Mountain no DST ... America/Phoenix
    			 Pacific ........... America/Los_Angeles
    			 Alaska ............ America/Anchorage
    			 Hawaii ............ America/Adak
    			 Hawaii no DST ..... Pacific/Honolulu
    			 */
 
                $orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
                $employeeColumn = "CASE WHEN o.extrnid is null THEN e.id ELSE e.extrnid END";
                $orders = $this->db->rawQuery("select o.type, TIME_FORMAT(o.promisedtime, '%h:%i %p') as promisedFormatted, o.promisedtime, o.startdate, COALESCE(o.extrnid,concat('C',o.id)) as id, cu.businessname, co.firstname, co.lastname, co.phone1, v.year, v.make, v.model, v.trim, v.license, o.status, GROUP_CONCAT(e.name SEPARATOR ', ') as name from `order` o left join contact co on (o.contact_id = COALESCE(co.extrnid, co.id) and o.store_id = co.store_id) left join customer cu on (co.customer_id = COALESCE(cu.extrnid, cu.id) and co.store_id = cu.store_id) left join vehicle v on (o.vehicle_id= COALESCE(v.extrnid, v.id) and o.store_id=v.store_id) left join order_teammember ot on ($orderColumn=ot.order_id and o.store_id=ot.store_id) left join teammember tm on (ot.teammember_id = tm.id and ot.store_id=tm.store_id) left join employee e on (tm.employee_id = $employeeColumn and tm.store_id=e.store_id) where o.type in ('W','E') and o.store_id=? group by COALESCE(o.extrnid,o.id), o.store_id order by o.status desc, o.promisedtime asc", array($storeId));
                

    			$openOrders = array("orders" => array(), "estimates" => array());
    			date_default_timezone_set('America/New_York');
    			foreach($orders as $o) {
    				if($o["promisedtime"] != null ) {
    					$o["minstopromised"] = round((strtotime($o["promisedtime"]) - time())/60,0);
    				}
    				if($o["type"] == "W") {
    					$openOrders["orders"][] = $o;
    				} else {
    					$openOrders["estimates"][] = $o;
    				}
    			}

                $openOrders['requestedAppointments'] = [];
                $requestedAppointments = $this->db->rawQuery("select * from appointment where order_id is null and store_id=?", array($storeId));
                if ($requestedAppointments) {
                    $openOrders['requestedAppointments'] = $requestedAppointments;
                }

    			return $this->handleRefreshToken($openOrders);
    		} elseif ($this->verb == "byVehicle") {
    			$orders = $this->db->rawQuery("select o.*, COALESCE(o.extrnid,concat('C',o.id)) as id, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join order_teammember ot on (o.id=ot.order_id and o.store_id=ot.store_id) left join teammember tm on (ot.teammember_id=tm.id and ot.store_id=tm.store_id) left join employee e on (tm.employee_id = e.id) where type in ('I','W') and o.vehicle_id = ? and o.store_id = ? order by type desc, updated desc", array($this->args[0], $storeId));
    			return $this->handleRefreshToken($orders);
    		} elseif ($this->verb == "noVehicle") {
    			$orders = $this->db->rawQuery("select o.*, COALESCE(o.extrnid,concat('C',o.id)) as id, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join order_teammember ot on (o.id=ot.order_id and o.store_id=ot.store_id) left join teammember tm on (ot.teammember_id=tm.id and ot.store_id=tm.store_id) left join employee e on (tm.employee_id = e.id) where type in ('I','W') and o.vehicle_id is null and o.contact_id in (select id from contact where customer_id=(select customer_id from contact where id=? and store_id=?)) and o.store_id=? order by type desc, updated desc", array($this->args[0], $storeId, $storeId));
    			return $this->handleRefreshToken($orders);
    		} elseif ($this->verb == "revision") {    
                $column = "extrnid";   
                $orderId = explode('/', $_GET['request'])[2];      
                if (strpos($orderId, 'C') !== false) {     
                    $column = "id";        
                    $orderId = str_replace('C', '', $orderId);     
                }  
   
                $rev = $this->db->rawQueryValue("select optcounter from `order` where $column=? and store_id = ? limit 1", array($orderId, $storeId));  
                return array("rev" => (int)$rev);  
            } elseif ($this->verb == "appointments") {
                $contactColumn = "CASE WHEN o.extrnid is null THEN c.id ELSE c.extrnid END";
                $customerColumn = "CASE WHEN c.extrnid is null THEN cu.id ELSE cu.extrnid END"; 
                $orders = $this->db->rawQuery("select o.type, DATE_FORMAT(o.startdate, '%m/%d/%Y') as start_date, o.starttime, COALESCE(o.extrnid,concat('C',o.id)) as id, o.customernotes, (CASE o.status WHEN '00' THEN 'blue' WHEN '50' THEN 'red' WHEN '60' THEN 'grey' ELSE 'green' END) as color, COALESCE(o.duration, 60) as duration, c.firstname, c.lastname, c.phone1, c.phone2, c.phone3 from `order` o left join contact c on (o.contact_id = COALESCE(c.extrnid, c.id) and o.store_id = c.store_id) left join customer cu on (c.customer_id = COALESCE(cu.extrnid, cu.id) and c.store_id = cu.store_id) where o.type in ('W','I','A') and o.starttime is not null and o.startdate is not null and o.store_id=? group by COALESCE(o.extrnid,o.id)", array($storeId));
                date_default_timezone_set('America/New_York');
                $openWorkOrders = array();
                foreach($orders as $i => $o) {
                    $datetime = new DateTime($o['start_date']. ' ' .$o['starttime']);
                    $start = $datetime->format(DateTime::ATOM);
                    $datetime->modify("+{$o['duration']} minutes");
                    $end = $datetime->format(DateTime::ATOM);
                    $openWorkOrders[$i]['title'] = '#'.$o['id'] . ": " . $o["firstname"] . " " . $o["lastname"] . " - " . $o["customernotes"];
                    $openWorkOrders[$i]['poptitle'] = '#'.$o['id'];
                    $phones = "";
                    if($o["phone1"] != null) {
                        $phones .= "Phone 1: " . $o["phone1"] . "<br />";
                    }
                    if($o["phone2"] != null) {
                        $phones .= "Phone 2: " . $o["phone2"] . "<br />";
                    }
                    if($o["phone3"] != null) {
                        $phones .= "Phone 3: " . $o["phone3"] . "<br />";
                    }
                    $openWorkOrders[$i]['description'] = "<strong>" . $o["firstname"] . " " . $o["lastname"] . "</strong><br />" . $phones . "<br />" . $o["customernotes"];
                    $openWorkOrders[$i]['start'] = $start;
                    $openWorkOrders[$i]['end'] = $end;
                    $openWorkOrders[$i]['url'] = 'workorderedit.php?orderId='.$o['id'].'&store='.$storeId;
                    $openWorkOrders[$i]['color'] = $o['color'];
                }
                return $openWorkOrders;
            } elseif ($this->verb == 'ordersReport') {
                if(!isset($_GET['start'])) {
                    // start of the day
                    $startTimeStamp = strtotime('midnight', time());
                    $startTime = date('Y-m-d H:i:s', $startTimeStamp);;
                } else {
                    $startTime = $_GET['start'] . " 00:00:00";
                }

                if(!isset($_GET['end'])) {
                    // end of the day
                    $endTimeStamp = strtotime('tomorrow', time()) - 1;
                    $endTime = date('Y-m-d H:i:s', $endTimeStamp);
                } else {
                    $endTime = $_GET['end'] . " 23:59:59";
                }
                
                $contactColumn = "CASE WHEN o.extrnid is null THEN co.id ELSE co.extrnid END";
                $customerColumn = "CASE WHEN o.extrnid is null THEN cu.id ELSE cu.extrnid END";
                $orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
                
                $orders = $this->db->rawQuery("SELECT st.id as store_id, st.identifier as nodeName, SUM(o.ordertotal-o.ordertax) as orderTotal, SUM(o.ordertax) as orderTax, SUM(opi.amount) as orderAmount FROM `order` o LEFT JOIN store st ON (o.store_id = st.id) LEFT JOIN orderpayinfo opi ON (o.id = opi.order_id AND o.store_id = opi.store_id) LEFT JOIN user_store us on (us.store_id=st.id) WHERE us.user_id = ? AND o.updated BETWEEN ? AND ? GROUP BY st.identifier ", array($this->user->userId,$startTime, $endTime));
                $oitems = $this->db->rawQuery("SELECT o.store_id, ROUND(SUM(CASE WHEN oi.cost IS NULL OR oi.cost = '' THEN 0 ELSE oi.cost END),2) AS cost FROM `order` o LEFT JOIN orderitem oi ON ( $orderColumn = oi.order_id AND o.store_id = oi.store_id) LEFT JOIN contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) LEFT JOIN customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) LEFT JOIN user_store us on (us.store_id=o.store_id) WHERE us.user_id = ? AND o.type = 'I' AND (cu.internal = 0 OR cu.internal is null) AND o.updated >=  ? AND o.updated <= ? group by o.store_id", array($this->user->userId,$startTime,$endTime));
                
                foreach($orders as $k=>$o) {
                    foreach($oitems as $oi) {
                        if($o['store_id'] == $oi['store_id']) {
                            $orders[$k]['cog'] = $oi['cost'];
                            $orders[$k]['margin'] = round($oi['cost']/$o['orderTotal'],2);
                        }
                    }
                }

                return $this->handleRefreshToken($orders);
            } else {
    			$column = "extrnid";
                $orderId = explode('/', $_GET['request'])[1];

                if (strpos($orderId, 'C') !== false) {
                    $column = "id";
                    $orderId = str_replace('C', '', $orderId);
                }

    			$order  = $this->db->rawQuery("select * from `order` where $column = ? and store_id = ?", array($orderId,$storeId));
                $store  = $this->db->rawQueryOne('select * from store where id = ?', array($storeId));
    			$retArray = array();
    			foreach($order as $o) {
    				$obj = $o;
                    $updated_date = new DateTime($obj['updated']);
                    $contact = $this->db->rawQueryOne("select *, COALESCE(extrnid, id) as id from contact where COALESCE(extrnid, id) = ? and store_id = ? limit 1", array($o['contact_id'],$storeId) );
    				$customer = $this->db->rawQueryOne("select *, COALESCE(extrnid, id) as id from customer where COALESCE(extrnid, id) = ? and store_id = ? limit 1", array($contact['customer_id'],$storeId) );
    				$obj["customer"] = $customer;
    				$obj["customer"]["contact"] = $contact;
    				if($o["vehicle_id"]) {
    					$obj["vehicle"] = $this->db->rawQueryOne("select *, COALESCE(extrnid, id) as id from vehicle where COALESCE(extrnid, id) = ? and store_id = ? limit 1", array($o['vehicle_id'],$storeId) );
    				}
    				$itemList = $this->db->rawQuery("select * from orderitem where order_id = ? and store_id = ? ", array($orderId,$storeId) );
    				$obj["items"] = $itemList;
                    $teammemberList = $this->db->rawQuery("select * from order_teammember where order_id = ? and store_id = ? ", array($orderId,$storeId) );
                    $obj["teammembers"] = $teammemberList;
    				$paymentList = array();
    				$paymethod = "";
    				$orderPay = $this->db->rawQuery("select * from orderpayinfo opi left join paymentmethod pm on (opi.paymentmethod_id=pm.id and opi.store_id=pm.store_id)  where opi.order_id = ? and opi.store_id = ?", array($orderId,$storeId));
    				foreach($orderPay as $p) {
    					$paymentList[] = $p;
    					if($paymethod != "") {
    						$paymethod .= ", ";
    					}
    					$paymethod .= $p["name"];
    					if(isset($p["checknumber"])) {
    						$paymethod .= " #" . $p["checknumber"];
    					}
    				}
    				if(count($paymentList) > 0) {
    					$obj["payments"] = $paymentList;
    					$obj["paymethod"] = $paymethod;
    				}

                    $obj['show_reference_no'] = $store['show_reference_no'];
                    if ($store['show_reference_no']) {
                        $variables = array(
                            '{id}' => $obj['id'],
                            '{date}' => $updated_date->format('Y-m-d'),
                            '{margin}' => (isset($obj['ordermargin']))? $obj['ordermargin']: '0.00',
                            '{tax}' => $obj['ordertax'],
                            '{total}' => $obj['ordertotal'],
                        );

                        $obj['reference_number'] = strtr($store['reference_number'], $variables);
                    }

    				$retArray[] = $obj;
    			}
    			return $this->handleRefreshToken($retArray);
    		}
    	} elseif($this->method == 'PUT'){
            if($this->verb == "orderDelete") {
                $column = "extrnid";
                $order_id = $this->args[0];
                if (strpos($order_id, 'C') !== false) {
                    $order_id = str_replace('C', '', $order_id);
                    $column = "id";
                }
                $store_id = $_GET['store'];
                $stock_itemtype_ids = '3, 4';

                // Remove reserved inventory from deleted order
                $this->db->objectBuilder();
                $inventoryList = $this->db->rawQuery("select * from inventory where partnumber in (select partnumber from orderitem where itemtype_id in ($stock_itemtype_ids) and order_id = ? and store_id = ?) and store_id = ?", array($order_id, $store_id, $store_id));
                $this->db->objectBuilder();
                $orderItems = $this->db->rawQuery("select * from orderitem where itemtype_id in ($stock_itemtype_ids) and order_id = ? and store_id = ?", array($order_id, $store_id));
                
                foreach($inventoryList as $inv) {
                    foreach($orderItems as $oi) {
                        if($inv->partnumber == $oi->partnumber) {
                            $this->db->where('id', $inv->id);
                            $inv->reserved -= $oi->quantity;
                            $inv->is_updated = true;
                            $this->db->update('inventory', get_object_vars($inv));
                            
                            // Inserting deleted items into deleted_data table for syncing
                            if ($oi->extrnid) {
                                $row = array(
                                    'tbl' => 'orderitem',
                                    'delete_column' => 'id',
                                    'delete_value' => $oi->extrnid,
                                    'store_id' => $oi->store_id,
                                );
                                $this->db->insert('deleted_data', $row);
                            }
                            // delete order item
                            $this->db->where('id', $oi->id);
                            $this->db->delete('orderitem');
                        }
                    }
                }
            
                // set type of order to 'D'
                $this->db->where($column, $order_id);
                $this->db->where('store_id', $store_id);
                $this->db->update('order', array('type' => 'D', 'is_updated' => true));
            
                return $this->handleRefreshToken(array('success' => true));
            } else {
                $column = "extrnid";
                $orderId = explode('/', $_GET['request'])[1];

                if (strpos($orderId, 'C') !== false) {
                    $column = "id";
                    $orderId = str_replace('C', '', $orderId);
                }

                $body = json_decode($this->file);
                $this->db->objectBuilder();
                $order  = $this->db->rawQueryOne("select * from `order` where $column = ? and store_id = ?", array($orderId, $body->store));
                if($order->type == "I") { return "Order already invoiced"; }

                $order->updated = date('Y-m-d G:i:s');
                if(isset($body->tickettype)) {
                    $order->type = $body->tickettype;
                }
                if(isset($body->ticketstatus)) {
                    $order->status = $body->ticketstatus;
                }
                if(isset($body->promisedtime)) {
                    $order->promisedtime = $body->promisedtime;
                }
                
                if(isset($body->startdate) && $body->startdate != null) {
                    $order->startdate = $body->startdate;
                } else {
                    $order->startdate = null;
                }
                    
                if(isset($body->starttime) && $body->starttime != null) {
                    $order->starttime = $body->starttime;
                } else {
                    $order->starttime = null;
                }
                
                if(isset($body->duration) && $body->duration != null) {
                    $order->duration = $body->duration;
                } else {
                    $order->duration = null;
                }
            
                if(isset($body->customernotes)) {
                    $order->customernotes = $body->customernotes;
                }
            
                if(isset($body->mileage)) {
                    $order->mileage = $body->mileage;
                }
            
                if(isset($body->ordertotal)) {
                    $order->ordertotal = $body->ordertotal;
                }
            
                if(isset($body->ordertax)) {
                    $order->ordertax = $body->ordertax;
                }
            
                if(isset($body->ordermargin)) {
                    $order->ordermargin = $body->ordermargin;
                }
            
                $order->contact_id = $body->contact_id;
            
                if(isset($body->vehicle_id) && $body->vehicle_id > 0) {
                    $order->vehicle_id = $body->vehicle_id;
                }
                
                $reduceInventory = false;
                if(isset($body->payments) && count($body->payments) > 0) {
                    $order->type = "I";
                    if(isset($order->vehicle_id) && isset($order->mileage)) {
                        $vehicle = new stdClass();
                        $vehicle->mileage = $order->mileage;
                        $vehicle->is_updated = true;
                        $this->db->where('COALESCE(extrnid, id)', $order->vehicle_id);
                        $this->db->where('store_id', $body->store);
                        $this->db->update('vehicle', get_object_vars($vehicle));
                    }
                    $reduceInventory = true;
                }
            
                // Remove reserved inventory from previous items
                $stock_itemtype_ids = '3,4';
                $this->db->objectBuilder();
                $inventoryList = $this->db->rawQuery("select * from inventory where partnumber in (select partnumber from orderitem where itemtype_id in ($stock_itemtype_ids) and order_id = ? and store_id = ?) and store_id = ?", array($orderId, $body->store, $body->store));
                $this->db->objectBuilder();
                $partList = $this->db->rawQuery("select * from orderitem where itemtype_id in ($stock_itemtype_ids) and order_id = ? and store_id = ?", array($orderId, $body->store));
                foreach($inventoryList as $inv) {
                    foreach($partList as $part) {
                        if($inv->partnumber == $part->partnumber) {
                            $this->db->where('id', $inv->id);
                            $inv->reserved -= $part->quantity;
                            $inv->is_updated = true;
                            $this->db->update('inventory', get_object_vars($inv));
                        }
                    }
                }
            
                $order->optcounter = $order->optcounter + 1;
                $order->store_id = $body->store;
                $order->is_updated = true;
                $this->db->where($column, $orderId);
                $this->db->where('store_id', $body->store);
                $this->db->update('order', get_object_vars($order));

                // order_teammember
                $this->db->map('id');
                $order_tm = $this->db->rawQuery("select id, teammember_id from order_teammember where order_id = ? and store_id = ?", array($orderId, $body->store));
                if(isset($body->teammember_id) && !empty($body->teammember_id)) {
                    foreach($body->teammember_id as $tmid) {
                        $teammember = $this->db->rawQueryOne("select * from teammember where id = ? and store_id = ?", array($tmid, $body->store));
                        
                        $order_tm_id = array_search($tmid, $order_tm);
                        if(!$order_tm_id) {
                            $order_teammember = array(
                                'store_id' => $body->store,
                                'teammember_id' => $teammember['id'],
                                'order_id' => $orderId,
                            );
                            $this->db->insert('order_teammember', $order_teammember);
                        }

                        unset($order_tm[$order_tm_id]);
                    }
                }

                if ($order_tm && !empty($order_tm)) {
                    foreach ($order_tm as $id => $tmid) {
                        // Inserting deleted teammember into deleted_data table for syncing
                        if ($order->extrnid) {
                            $row = array(
                                'tbl' => 'order_teammember',
                                'delete_column' => 'order_id',
                                'delete_value' => $order->extrnid,
                                'store_id' => $body->store,
                            );
                            $this->db->insert('deleted_data', $row);
                        }

                        // delete teammember
                        $this->db->where('id', $id);
                        $this->db->delete('order_teammember');
                    }
                }

                // Inserting deleted items into deleted_data table for syncing
                if ($order->extrnid) {
                    $row = array(
                        'tbl' => 'orderitem',
                        'delete_column' => 'order_id',
                        'delete_value' => $order->extrnid,
                        'store_id' => $body->store,
                    );
                    $this->db->insert('deleted_data', $row);
                }
                // orderitems
                $this->db->where("order_id", $orderId);
                $this->db->where("store_id", $body->store);
                $this->db->delete('orderitem');
                foreach($body->items as $k=>$i) {
                    $item = new stdClass();
                    $item->order_id = $orderId;
                    $item->itemtype_id = $i->type;
                    $item->partnumber = $i->partnumber;
                    $item->description = $i->description;
                    $item->quantity = $i->quantity;
                    $item->retail = $i->retail;
                    $item->cost = $i->cost;
                    $item->taxcat = $i->taxcat;
                    if(isset($i->dotnumber) && $i->dotnumber != "") {
                        $item->dotnumber = $i->dotnumber;
                    }
                
                    if(isset($i->vendor_id) && $i->vendor_id != "") {
                        $item->vendor_id = $i->vendor_id;
                    }
                
                    if(isset($i->invoicenumber) && $i->invoicenumber != "") {
                        $item->invoicenumber = $i->invoicenumber;
                    }
                
                    if(isset($i->tax) && $i->tax != "") {
                        $item->tax = $i->tax;
                    }

                    $item->store_id = $body->store;
                    $this->db->insert('orderitem', get_object_vars($item));
                    
                    if($i->type == 3 || $i->type == 4) {
                        // Reserve inventory for new items
                        $this->db->objectBuilder();
                        $inv = $this->db->where('partnumber', $i->partnumber)->where('store_id', $body->store)->getOne('inventory');
                        if($inv != null) {
                            if(!$reduceInventory) {
                                $inv->reserved += $i->quantity;
                                $inv->is_updated = true;
                                $this->db->update('inventory', get_object_vars($inv));
                            } else {
                                $inv->quantity -= $i->quantity;
                                $inv->is_updated = true;
                                $this->db->update('inventory', get_object_vars($inv));
                            }
                        }
                    } elseif (($i->type == 1 || $i->type == 2) && $reduceInventory) {
                        $this->db->objectBuilder();
                        $invoice = $this->db->where('number', $item->invoicenumber)->where('vendor_id', $item->vendor_id)->where('store_id', $body->store)->getOne('invoice');
                        if($invoice == null) {
                            $invoice = new stdClass();
                            $invoice->number = $item->invoicenumber;
                            $invoice->vendor_id = $item->vendor_id;
                            $invoice->created = date('Y-m-d G:i:s');
                            $invoice->paid = 0;
                            $invoice->store_id = $body->store;
                            $invoice->id = $this->db->insert('invoice', get_object_vars($invoice));
                        }
                    
                        $item = new stdClass();
                        $item->invoice_id = $invoice->id;
                        $item->partnumber = $i->partnumber;
                        $item->quantity = $i->quantity;
                        $item->cost = $i->cost;
                        $item->store_id = $body->store;
                        $this->db->insert('invoiceitem', get_object_vars($item));
                    }
                }
            
                if(isset($body->payments)) {
                    $this->db->where('order_id', $orderId)->where('store_id', $body->store)->get('orderpayinfo');
                    $orderPays = $this->db->count;
                    if($orderPays < 1) {
                        foreach($body->payments as $i) {
                            $this->db->objectBuilder();
                            $paymentMethod = $this->db->where('COALESCE(extrnid, id)', $i->method)->where('store_id', $body->store)->getOne('paymentmethod');
                            $payment = new stdClass();
                            $payment->order_id = $orderId;
                            $payment->paymentmethod_id = $i->method;
                            $payment->amount = $i->amount;
                            $payment->store_id = $body->store;
                            if($paymentMethod->open == 0) {
                                $payment->paydate = date('Y-m-d G:i:s');
                                $payment->closedmethod = $i->method;
                            }
                            if(isset($i->checknumber)) {
                                $payment->checknumber = $i->checknumber;
                            }
                            $this->db->insert('orderpayinfo', get_object_vars($payment));
                        }
                    }
                }
        		return $this->handleRefreshToken(array("id"=>$orderId));
            }
    	} elseif($this->method == 'DELETE'){
    		$order  = R::findOne('order', ' endpoint = ? ', array( $this->verb ) );
    		R::trash($order);
    		return $this->handleRefreshToken(array("success" => true));
    	}
    
    }

    protected function appointment() {
        if($this->user == null) {
            return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
        }
        if($this->method == 'PUT') {
            if($this->verb == 'decline') {
                $appointmentId = $this->args[0];

                $this->db->objectBuilder();
                $appointment = $this->db->rawQueryOne('select * from appointment where id = ? limit 1', array($appointmentId));
                $this->db->where('id', $appointment->id);

                $appointment->order_id = -1;
                $this->db->update('appointment', get_object_vars($appointment));
                return array('success' => true);
            }
        }
    }
    
    protected function customer() {
    	if($this->user == null) {
    		return $this->handleRefreshToken(array("data" => array("error" => "User session timed out"), "statuscode" => 401));
    	}
    	if($this->method == 'POST') {
    		$customer = new stdClass();
    		$body = @file_get_contents('php://input');
    		$body = json_decode($body);
    			
    		$customer->usertype = $body->usertype;
    		$customer->taxexempt = $body->taxexempt;
            $customer->store_id = $body->store;
    			
    		if(isset($body->taxexemptnum)) {
    			$customer->taxexemptnum = $body->taxexemptnum;
    		}
    			
    		if(isset($body->businessname)) {
    			$customer->businessname = $body->businessname;
    		}
    			
    		if(isset($body->addressline1)) {
    			$customer->addressline1 = $body->addressline1;
    		}
    
    		if(isset($body->addressline2)) {
    			$customer->addressline2 = $body->addressline2;
    		}
    
    		if(isset($body->addressline3)) {
    			$customer->addressline3 = $body->addressline3;
    		}
    			
    		if(isset($body->city)) {
    			$customer->city = $body->city;
    		}
    			
    		if(isset($body->state)) {
    			$customer->state = $body->state;
    		}
    			
    		if(isset($body->zip)) {
    			$customer->zip = $body->zip;
    		}
    			
    		if(isset($body->internal)) {
    			$customer->internal = $body->internal;
    		} else {
    			$customer->internal = 0;
    		}
    
    
    		$contact = new stdClass();;
    		$contact->firstname = $body->contact->firstname;
    		$contact->lastname = $body->contact->lastname;
    		$contact->phone1type = $body->contact->phone1type;
    		$contact->phone1 = str_replace(' ', '', $body->contact->phone1);
    			
    		if(isset($body->contact->phone2)) {
    			$contact->phone2type = $body->contact->phone2type;
    			$contact->phone2 = $body->contact->phone2;
    		}
    			
    		if(isset($body->contact->phone3)) {
    			$contact->phone3type = $body->contact->phone3type;
    			$contact->phone3 = $body->contact->phone3;
    		}
    			
    		if(isset($body->contact->email)) {
    			$contact->email = $body->contact->email;
    		}
    			
            if(isset($body->contact->isPrimary)) {
                $contact->isprimary = "true";
            } else {
                $contact->isprimary = "false";
            }

    		$contact->store_id = $body->store;
    			
            $customer_id = $this->db->insert('customer', get_object_vars($customer));
            
            if($customer_id) {
                $contact->customer_id = $customer_id;
                $contact_id = $this->db->insert('contact', get_object_vars($contact));
            }    
    			
    		return $this->handleRefreshToken(array("id"=>$customer_id,"contact_id"=>$contact_id));
    	} elseif($this->method == 'GET'){
    		if(!isset($_GET['store'])) {
    			return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
    		}
    		$storeId = $_GET['store'];
    		if(!$this->isUserAuthorizedForStore($storeId)) {
    		    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
    		}

    		if($this->verb == "byVehicle") {
    			$contactsData = $this->db->rawQuery("SELECT customer.businessname, contact.*, COALESCE(contact.extrnid, contact.id) as id FROM customer INNER JOIN contact on (contact.customer_id = COALESCE(customer.extrnid, customer.id) and contact.store_id = customer.store_id) INNER JOIN vehicle on (vehicle.customer_id = COALESCE(customer.extrnid, customer.id) and vehicle.store_id = customer.store_id) WHERE vehicle.active=1 AND COALESCE(vehicle.extrnid, vehicle.id) = ? AND vehicle.store_id = ?", array($this->args[0], $storeId));

                $retArray = array();
    			foreach ($contactsData as $key => $contact) {
                    $row = $this->db->rawQueryOne("
                        SELECT * FROM customer
                        WHERE COALESCE(extrnid, id) = ? and store_id = ?
                        ", array($contact['customer_id'], $storeId) );
                    $contactsData[$key] = $row;
                    $contactsData[$key]['contact'] = $contact;
                }

                $retArray['data'] = $contactsData;
    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "search") {
    			$findBy = "";
    			$argArray = array();
    			if(isset($_GET['firstName'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " contact.firstname = ? ";
    				$argArray[] = $_GET['firstName'];
    			}
    			if(isset($_GET['lastName'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " contact.lastname = ? ";
    				$argArray[] = $_GET['lastName'];
    			}
    			if(isset($_GET['phone'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " (contact.phone1 = ? || contact.phone2 = ? || contact.phone3 = ?) ";
    				$argArray[] = $_GET['phone'];
    				$argArray[] = $_GET['phone'];
    				$argArray[] = $_GET['phone'];
    			}
    			if(isset($_GET['businessname'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " customer.businessname like ? ";
    				$argArray[] = "%" . $_GET['businessname'] . "%";
    			}
    
    			$customer = null;
    			if($findBy != "") {
                    $findBy .= " AND";
                    $findBy .= " contact.store_id = ? ";
                    $argArray[] = $storeId;

    				$contactsData = $this->db->rawQuery("
    						SELECT customer.businessname, contact.* FROM contact
    						RIGHT JOIN customer on (COALESCE(customer.extrnid, customer.id) = contact.customer_id and customer.store_id =  contact.store_id)
    						WHERE $findBy
    						", $argArray );

                    foreach ($contactsData as $key => $contact) {
                        $row = $this->db->rawQueryOne("
                            SELECT * FROM customer
                            WHERE COALESCE(extrnid, id) = ? and store_id = ?
                            ", array($contact['customer_id'], $storeId) );
                        $contactsData[$key] = $row;
                        $contact['id'] = $contact['extrnid'] ?? $contact['id'];
                        $contactsData[$key]['contact'] = $contact;
                    }
    			}
                
                $retArray['data'] = array();
    			$retArray['data'] = $contactsData;

    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "all") {
    			$page = 1;
    			$limit = 25;
    			if(count($this->args) > 0) {
    				$page = $this->args[0];
    			}
    
    			$findBy = " ";
    			$argArray = array();
    			$argArray[] = $storeId;
    			if(isset($_GET['firstName'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " co.firstname = ? ";
    				$argArray[] = $_GET['firstName'];
    			}
    			if(isset($_GET['lastName'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " co.lastname = ? ";
    				$argArray[] = $_GET['lastName'];
    			}
    			if(isset($_GET['phone'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " (co.phone1 = ? || co.phone2 = ? || co.phone3 = ?) ";
    				$argArray[] = $_GET['phone'];
    				$argArray[] = $_GET['phone'];
    				$argArray[] = $_GET['phone'];
    			}
    			if(isset($_GET['businessname'])) {
    				if($findBy != "") {
    					$findBy .= " AND";
    				}
    				$findBy .= " cu.businessname like ? ";
    				$argArray[] = "%" . $_GET['businessname'] . "%";
    			}
    

    			$customerColumn = "CASE WHEN cu.extrnid is null THEN cu.id ELSE cu.extrnid END";

    			$retArray = array();
    			$retArray['total'] = $this->db->rawQueryValue("select count(co.lastname) from customer cu left join contact co on ($customerColumn = co.customer_id and cu.store_id = co.store_id) where co.store_id = ? and co.isprimary='true'" . $findBy . " limit 1", $argArray);
    			$argArray[] = (($page-1)*$limit);
    			$argArray[] = $limit;
    			$retArray['rows'] = $this->db->rawQuery("select COALESCE(cu.extrnid, concat('C',cu.id)) as id, co.firstname, co.lastname, co.phone1, cu.businessname, cu.addressline1, cu.city, cu.state, cu.zip from customer cu left join contact co on ($customerColumn = co.customer_id and cu.store_id = co.store_id) where co.store_id = ? and co.isprimary='true'" . $findBy . " order by COALESCE(cu.businessname,co.lastname) asc LIMIT ?,?",$argArray);
    			$retArray['page'] = $page;
    			$retArray['totalPages'] = ceil($retArray['total']/$limit);
    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "detail") {
    			
    			$contactColumn = "CASE WHEN co.extrnid is null THEN co.id ELSE co.extrnid END";
    			$customerColumn = "CASE WHEN cu.extrnid is null THEN cu.id ELSE cu.extrnid END";
    			
    			$retArray = array();
    			if(isset($_GET['invoiceId'])) {
    				$idColumn = "extrnid";
    				if (strpos($_GET['invoiceId'], 'C')) {
    					$idColumn = "id";
    				}
    				$customerId = $this->db->rawQueryValue("select customer_id from customer cu left join contact co on (co.id=co.customer_id and cu.store_id=co.store_id) left join `order` o on ($contactColumn = o.contact_id and co.store_id=o.store_id) where cu.store_id=? and o.$idColumn=? limit 1", array($storeId,$_GET['invoiceId']));
    				$customer = $this->db->rawQueryOne("select * from customer where store_id=? and id=?",array($storeId,$customerId));
    				$this->args[0] = $customerId;
    			} else {
    				$idColumn = "extrnid";
                    $customer_id = $this->args[0];
    				if (strpos($customer_id, 'C') !== false) {
                        $customer_id = str_replace('C', '', $customer_id);
    					$idColumn = "id";
    				}
    				$customer = $this->db->rawQueryOne("select * from customer where store_id=? and $idColumn=?",array($storeId, $customer_id));
    			}
    			$retArray['customer'] = $customer;
    			$customerId = $customer_id;
    			if($customer['extrnid'] != null) {
    				$customerId = $customer['extrnid'];
    			}
    			$vehicles = $this->db->rawQuery("select *, COALESCE(extrnid, id) as id from vehicle where customer_id=? and store_id=? order by year desc, make asc, model", array($customerId,$storeId));
    			$contacts = $this->db->rawQuery("select * from contact where customer_id=? and store_id=? order by lastname asc", array($customerId,$storeId));
    			
    			$orderContactColumn = "CASE WHEN o.extrnid is null THEN co.id ELSE co.extrnid END"; 
                $orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
                $employeeColumn = "CASE WHEN o.extrnid is null THEN e.id ELSE e.extrnid END";

    			$orders = $this->db->rawQuery("select COALESCE(o.extrnid,concat('C',o.id)) as orderid, o.*, DATE_FORMAT(o.updated, '%m/%d/%Y') as orderdate, e.name as technician from `order` o left join contact co on (o.contact_id = $orderContactColumn and o.store_id = co.store_id) left join order_teammember ot on ($orderColumn=ot.order_id and o.store_id=ot.store_id) left join teammember tm on (ot.teammember_id = tm.id and ot.store_id=tm.store_id) left join employee e on (tm.employee_id = $employeeColumn) where type in ('I','W') and co.customer_id = ? and co.store_id=? order by type desc, updated desc", array($customerId, $storeId));
    			$retArray['vehicles'] = $vehicles;
    			
    			$vExtrnidToId = array();
    			foreach($vehicles as $v) {
    				if($v['extrnid'] != null) {
    					$vExtrnidToId[$v['extrnid']] = $v['id'];
    				}
    			}

    			$retArray['contacts'] = $contacts;
    			$retArray['orders'] = array();
    			foreach($orders as $o) {
    				$vehicleId = $o['vehicle_id'];

    				if($o['extrnid'] != null && array_key_exists($vehicleId,$vExtrnidToId)) {
    					$vehicleId = $vExtrnidToId[$vehicleId];
    				}
    				if(!array_key_exists($vehicleId,$retArray['orders'])) {
    					$retArray['orders'][$vehicleId] = array();
    				}
    				$retArray['orders'][$vehicleId][] = $o;
    			}
    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "checkcache") {
                $idColumn = "extrnid";
                $customer_id = $this->args[0];
                if (strpos($customer_id, 'C') !== false) {
                    $customer_id = str_replace('C', '', $customer_id);
                    $idColumn = "id";
                }

                $caching  = $this->db->rawQueryOne("select cached_version from customer where $idColumn = ? and store_id = ?", array($customer_id, $storeId));
                return $caching['cached_version'];
            }  else {
    			if(count($this->args) > 0) {
    				$customer = R::findOne('customer', ' id = ? ', array($this->args[0]) );
    				return json_decode($customer->__toString());
    			} else {
    				$customer = R::findAll('customer');
    				$retArray = array();
    				foreach($customer as $c) {
    					$retArray[] = json_decode($c->__toString());
    				}
    				return $this->handleRefreshToken($retArray);
    			}
    		}
    			
    	} elseif($this->method == 'PUT'){
    		$body = json_decode($this->file);
    		if(!isset($body->customer_id)) {
    			return "Missing parameter: customer_id";
    		}
    			
    		$customer = new stdClass();
            $column = "extrnid";
            if (strpos($body->customer_id, 'C') !== false) {
                $body->customer_id = str_replace('C', '', $body->customer_id);
                $column = "id";
            }
    			
    		$customer->usertype = $body->usertype;
    		$customer->taxexempt = $body->taxexempt;
    
    		if(isset($body->taxexemptnum)) {
    			$customer->taxexemptnum = $body->taxexemptnum;
    		} else {
                $customer->taxexemptnum = null;
            }
    
    		if(isset($body->businessname)) {
    			$customer->businessname = $body->businessname;
    		} elseif($customer->usertype = 'P') {
    			$customer->businessname = null;
    		}
    
    		if(isset($body->addressline1)) {
    			$customer->addressline1 = $body->addressline1;
    		}
    			
    		if(isset($body->addressline2)) {
    			$customer->addressline2 = $body->addressline2;
    		} else {
                $customer->addressline2 = null;
            }
    			
    		if(isset($body->addressline3)) {
    			$customer->addressline3 = $body->addressline3;
    		} else {
                $customer->addressline3 = null;
            }
    
    		if(isset($body->city)) {
    			$customer->city = $body->city;
    		}
    
    		if(isset($body->state)) {
    			$customer->state = $body->state;
    		}
    
    		if(isset($body->zip)) {
    			$customer->zip = $body->zip;
    		}
    			
    		if(isset($body->internal)) {
    			$customer->internal = $body->internal;
    		} else {
    			$customer->internal = 0;
    		}
    
    		if(isset($body->contact_id)) {
                $this->db->objectBuilder();
    			$contact = $this->db->rawQueryOne('select * from contact where COALESCE(extrnid, id) = ? and store_id = ? limit 1', array($body->contact_id, $body->store));
    				
    			// if($contact->customer_id != $body->customer_id) {
    			// 	// return "Invalid parameter: contact_id";
    			// }
    			$contact->firstname = $body->contact->firstname;
    			$contact->lastname = $body->contact->lastname;
    			$contact->phone1type = $body->contact->phone1type;
    			$contact->phone1 = $body->contact->phone1;
    				
    			if(isset($body->contact->phone2)) {
    				$contact->phone2type = $body->contact->phone2type;
    				$contact->phone2 = $body->contact->phone2;
    			}
    				
    			if(isset($body->contact->phone3)) {
    				$contact->phone3type = $body->contact->phone3type;
    				$contact->phone3 = $body->contact->phone3;
    			}
    				
    			if(isset($body->contact->email)) {
    				$contact->email = $body->contact->email;
    			}
    				
                if(isset($body->contact->isPrimary)) {
                    // setting only this contact isprimary to true
                    $this->unsetPrimaryContacts($body->customer_id, $body->store, $body->contact_id);
                    $contact->isprimary = "true";
                }else {
                    $contact->isprimary = "false";
                }
    			
                $contact->is_updated = true;	
    			$this->db->where('COALESCE(extrnid, id)', $body->contact_id);    
                $this->db->update('contact', get_object_vars($contact));
    		}
            
            $customer->is_updated = true;
            $customer->cached_version = time();
            $this->db->where($column, $body->customer_id);    
    		$id = $this->db->update('customer', get_object_vars($customer));
    
    		return $this->handleRefreshToken(array("id"=>$id));
    	} elseif($this->method == 'DELETE'){
    		$customer  = R::findOne('customer', ' endpoint = ? ', array( $this->verb ) );
    		R::trash($customer);
    		return $this->handleRefreshToken(array("success" => true));
    	}
    
    }

    protected function contact() {
        if($this->user == null) {
            return $this->handleRefreshToken(array("data" => Array("error" => "User session timed out"), "statuscode" => 401));
        }
        if($this->method == 'POST') {
            $body = @file_get_contents('php://input');
            $body = json_decode($body);
            
            $contact = new stdClass();
            $contact->firstname = $body->firstname;
            $contact->lastname = $body->lastname;
            $contact->phone1type = $body->phone1type;
            $contact->phone1 = str_replace(' ', '', $body->phone1);
            
            if(isset($body->phone2)) {
                $contact->phone2type = $body->phone2type;
                $contact->phone2 = str_replace(' ', '', $body->phone2);
            }
            
            if(isset($body->phone3)) {
                $contact->phone3type = $body->phone3type;
                $contact->phone3 = str_replace(' ', '', $body->phone3);
            }
            
            if(isset($body->email)) {
                $contact->email = $body->email;
            }
            
            // Check if customer is central or external
            $this->db->where('id', $body->customer_id)->where('store_id', $body->store);
            $customer_extrnid = $this->db->getValue('customer', 'extrnid'); 
            $body->customer_id = $customer_extrnid ?? $body->customer_id;

            if(isset($body->isPrimary)) {
                // setting only this contact isprimary to true
                $this->unsetPrimaryContacts($body->customer_id, $body->store);
                $contact->isprimary = "true";
            } else {
                $contact->isprimary = "false";
            }

            $contact->customer_id = $body->customer_id;
            $contact->store_id = $body->store;
            
            $id = $this->db->insert('contact', get_object_vars($contact));
            $this->setCustomerCache($contact->customer_id, $body->store, time());
            
            return $this->handleRefreshToken(array("id"=>$id));
        } elseif($this->method == 'GET'){
        } elseif($this->method == 'PUT'){
            $body = json_decode($this->file);
            if(!isset($body->contact_id)) {
                return "Missing parameter: contact_id";
            }

            $this->db->objectBuilder();
            $contact = $this->db->rawQueryOne('select * from contact where id = ?', array($body->contact_id));
            
            if($contact->customer_id != $body->customer_id) {
                $body->customer_id = $contact->customer_id;
            }

            $contact->firstname = $body->firstname;
            $contact->lastname = $body->lastname;
            $contact->phone1type = $body->phone1type;
            $contact->phone1 = str_replace(' ', '', $body->phone1);

            if(isset($body->phone2)) {
                $contact->phone2type = $body->phone2type;
                $contact->phone2 = str_replace(' ', '', $body->phone2);
            } else {
                $contact->phone2 = null;
            }

            if(isset($body->phone3)) {
                $contact->phone3type = $body->phone3type;
                $contact->phone3 = str_replace(' ', '', $body->phone3);
            } else {
                $contact->phone3 = null;
            }

            if(isset($body->email)) {
                $contact->email = $body->email;
            } else {
               $contact->email = null; 
            }
 
            if(isset($body->isPrimary)) {
                // setting only this contact isprimary to true
                $this->unsetPrimaryContacts($body->customer_id, $body->store, $body->contact_id);
                $contact->isprimary = "true";
            } else {
                $contact->isprimary = "false";
            }

            $contact->customer_id = $body->customer_id;
            $contact->is_updated = true;

            $this->db->where('id', $body->contact_id);
            $id = $this->db->update('contact', get_object_vars($contact));
            $this->setCustomerCache($contact->customer_id, $body->store, time());

            return $this->handleRefreshToken(array("id"=>$id));
        } elseif($this->method == 'DELETE'){
        }
    }
    
    protected function store() {
    	if($this->user == null) {
    		return array("data" => array("error" => "User session timed out"), "statuscode" => 401);
    	}
    	if($this->method == 'POST') {
            $body = @file_get_contents('php://input');
            $body = json_decode($body, true);
            
            if($this->user->isAdmin != 1) {
                return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
            }

            if ($this->verb == "taxrate") {
                unset($body['taxrate_id']);
                $body['active'] = true;           
                $id = $this->db->insert('taxrate', $body);
                $this->setStoreCache($body['store_id'], time());
                return $this->handleRefreshToken(array("success" => $id));
            } elseif ($this->verb == "teammember") {
                $employee = array('name' => $body['name'], 'active' => true, 'store_id' => $body['store_id']);
                $employee_id = $this->db->insert('employee', $employee);

                $teammember = array(
                    'store_id' => $body['store_id'],
                    'employee_id' => $employee_id,
                    'role_id' => $body['role']
                );

                $id = $this->db->insert('teammember', $teammember);
                $this->setStoreCache($body['store_id'], time());
                return $this->handleRefreshToken(array("success" => $id));
            } elseif ($this->verb == "paymentmethod") {
                if ($body['default'] == 1) {
                    $paymentmethod = $this->db->rawQueryOne('select * from paymentmethod where store_id = ? and paymentmethod.default = 1', array($body['store_id']));
                    if ($paymentmethod) {
                        $this->db->where('id', $paymentmethod['id']);
                        $payment = array('default' => 0);
                        $this->db->update('paymentmethod', $payment);
                    }
                }
                
                $pm = array(
                    'store_id' => $body['store_id'], 
                    'name' => $body['name'], 
                    'paymenttype_id' => $body['paymenttype'], 
                    'open' => $body['open'], 
                    'default' => $body['default'], 
                    'active' => true
                );
                
                $id = $this->db->insert('paymentmethod', $pm);
                $this->setStoreCache($body['store_id'], time());

                return $this->handleRefreshToken(array("success"=>$id));
            }
    	} elseif($this->method == 'GET'){
    		if(!isset($_GET['store'])) {
    			return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
    		}
    		$storeId = $_GET['store'];
    		if(!$this->isUserAuthorizedForStore($storeId)) {
    		    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
    		}
    		if($this->verb == "details") {
    			$store = $this->db->rawQueryOne("select * from store where id=? limit 1", array($storeId));
    			$taxrates = $this->db->rawQuery("select * from taxrate where store_id = ? and active=true", array($storeId));
    			$itemtypes  = $this->db->rawQuery("select it.*, COALESCE(it.extrnid, it.id) as id from itemtype it where store_id = ?", array($storeId));
                $roles  = $this->db->rawQuery('select * from role');
    			$employees = $this->db->rawQuery('SELECT teammember.id, teammember.employee_id, teammember.role_id, employee.name, (SELECT role.name from role where role.id = teammember.role_id) as role_name from teammember left join employee on teammember.employee_id = COALESCE(employee.extrnid, employee.id) where employee.active=1 and teammember.store_id = employee.store_id and employee.store_id=?', array($storeId));
    			$vendors = $this->db->rawQuery("select vendor.*, COALESCE(extrnid, id) as id from vendor where  store_id = ? and active=1", array($storeId));
    			return $this->handleRefreshToken(array("store" => $store,"rates" => $taxrates,
    					"itemtypes" => $itemtypes, "team" => $employees, "vendors" => $vendors, "roles" => $roles
    			));
    		} elseif($this->verb == "paymentmethods") {
                $paymenttypes  = $this->db->rawQuery('select * from paymenttype');
    			$paymentmethods = $this->db->rawQuery('SELECT paymentmethod.*, COALESCE(paymentmethod.extrnid, paymentmethod.id) as id, (SELECT paymenttype.name from paymenttype where paymenttype.id = paymentmethod.paymenttype_id) as paymenttype_name from paymentmethod where paymentmethod.active=1 and paymentmethod.store_id=?', array($storeId));
                $returnArray = array("paymentmethods" => $paymentmethods, "paymenttypes" => $paymenttypes);
    			return $this->handleRefreshToken($returnArray);
    		} elseif($this->verb == "checkcache") {
                $caching  = $this->db->rawQueryOne('select cached_version from store where id = ?', array($storeId));
                return $caching['cached_version'];
            } else {
    			$itemtype  = R::findOne('customer', ' id = ? and store_id = ? ', array($this->args[0],$storeId) );
    			return $this->handleRefreshToken(json_decode($itemtype->__toString()));
    		}
    	} elseif($this->method == 'PUT'){
            if($this->user->isAdmin != 1) {
                return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
            }

            $body = json_decode($this->file, true);

            if($this->verb == "details") {
                if(!isset($body['address2'])) {
                    $body['address2'] = '';
                }

                if(!isset($body['fax'])) {
                    $body['fax'] = '';
                }

                if (!isset($body['reference_number'])) {
                    $body['reference_number'] = NULL;
                }

                $body['is_updated'] = true;
                $body['cached_version'] = time();
                $this->db->where('id', $body['id']);
                $updated = $this->db->update('store', $body);

                return $this->handleRefreshToken(array("success" => $updated));
            } elseif ($this->verb == "taxrate") {
                $body['is_updated'] = true;
                $this->db->where('id', $body['taxrate_id']);
                $this->db->where('store_id', $body['store_id']);
                unset($body['taxrate_id']);
                $updated = $this->db->update('taxrate', $body);    
                $this->setStoreCache($body['store_id'], time());

                return $this->handleRefreshToken(array("success" => $updated));
            } elseif($this->verb == "taxrateDelete") {
                $this->db->where("id", $this->args[0]);
                $this->db->where('store_id', $_GET['store_id']);
                $this->db->update('taxrate', array('active' => false, 'is_updated' => true));
                $this->setStoreCache($_GET['store_id'], time());
                return $this->handleRefreshToken(array("success" => true));
            } elseif ($this->verb == "teammember") {
                $this->db->where('COALESCE(extrnid, id)', $body['employee_id']);
                $this->db->where('store_id', $body['store_id']);
                $this->db->update('employee', array('name' => $body['name'], 'is_updated' => true));
                
                // teammember
                $this->db->where('employee_id', $body['employee_id']);
                $this->db->where('store_id', $body['store_id']);
                $id = $this->db->update('teammember', array('role_id' => $body['role'], 'is_updated' => true));
                $this->setStoreCache($body['store_id'], time());

                return $this->handleRefreshToken(array("success" => $id));
            } elseif ($this->verb == "teammemberDelete") {
                $this->db->where('COALESCE(extrnid, id)', $this->args[0]);
                $this->db->where('store_id', $_GET['store']);
                $this->db->update('employee', array('active' => false, 'is_updated' => true));
                $this->setStoreCache($_GET['store'], time());

                return $this->handleRefreshToken(array('success' => true));
            } elseif ($this->verb == "paymentmethod") {
                if ($body['default'] == 1) {
                    $paymentmethod = $this->db->rawQueryOne('select *, COALESCE(extrnid, id) as id from paymentmethod where store_id = ? and paymentmethod.default = 1', array($body['store_id']));
                    if ($paymentmethod) {
                        if ($paymentmethod['id'] != $body['paymentmethod_id']) {
                            $this->db->where('COALESCE(extrnid, id)', $paymentmethod['id']);
                            $this->db->where('store_id', $body['store_id']);
                            $payment = array('default' => 0, 'is_updated' => true);
                            $this->db->update('paymentmethod', $payment);
                        }
                    }
                }

                $pm = array(
                    'name' => $body['name'], 
                    'paymenttype_id' => $body['paymenttype'], 
                    'open' => $body['open'], 
                    'default' => $body['default'],
                    'is_updated' => true
                );

                $this->db->where('COALESCE(extrnid, id)', $body['paymentmethod_id']);
                $this->db->where('store_id', $body['store_id']);
                $id = $this->db->update('paymentmethod', $pm);
                $this->setStoreCache($body['store_id'], time());

                return $this->handleRefreshToken(array("success"=>$id));
            } elseif ($this->verb == "paymentmethodDelete") {
                $this->db->where('COALESCE(extrnid, id)', $this->args[0]);
                $this->db->where('store_id', $_GET['store']);
                $this->db->update('paymentmethod', array('active' => false, 'is_updated' => true));
                $this->setStoreCache($_GET['store'], time());

                return $this->handleRefreshToken(array('success' => true));
            }
    	} elseif($this->method == 'DELETE'){
    	}
    
    }
    
    protected function vendor() {
    	if($this->user == null) {
    		return array("data" => array("error" => "User session timed out"), "statuscode" => 401);
    	}
    	if($this->method == 'POST') {
            if(!isset($_GET['store'])) {
                return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
            }
            $storeId = $_GET['store'];
            if(!$this->isUserAuthorizedForStore($storeId)) {
                return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
            }

    		$body = @file_get_contents('php://input');
    		$body = json_decode($body);
    		$v = new stdClass();
    		$v->vendorname = $body->vendorname;
    		$v->store_id = $storeId;
    		$v->firstname = $body->firstname;
    		$v->lastname = $body->lastname;
    		$v->phone1 = $body->phone1;
    		if(isset($body->phone2) && $body->phone2 != "") {
    			$v->phone2 = $body->phone2;
    		}
    		if(isset($body->email) && $body->email != "") {
    			$v->email = $body->email;
    		}
    		$v->address1 = $body->address1;
    		if(isset($body->address2) && $body->address2 != "") {
    			$v->address2 = $body->address2;
    		}
    		$v->zip = $body->zip;
    		$v->city = $body->city;
    		$v->state = $body->state;
    		$v->active = $body->active;
    		$id = $this->db->insert('vendor', get_object_vars($v));
    			
    		return $this->handleRefreshToken(array("id"=>$id));
    	} elseif($this->method == 'GET'){
    		if(!isset($_GET['store'])) {
    			return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
    		}
    		$storeId = $_GET['store'];
    		if(!$this->isUserAuthorizedForStore($storeId)) {
    		    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
    		}
    		if($this->verb == "all") {
    			$vendors = $this->db->rawQuery("select COALESCE(extrnid,concat('C',id)) as vendorid, vendor.* from vendor where store_id=? order by vendorname", array($storeId));
                $res['data'] = $vendors;
    			return $this->handleRefreshToken($res);
    		} elseif($this->verb == "history") {
    			$rows = $this->db->rawQuery('SELECT COALESCE(i.extrnid,concat("C",i.id)) as id, i.number, DATE_FORMAT(i.created, "%m/%d/%Y") as date, i.vendor_id, i.paid, sum(ii.quantity*ii.cost) as total FROM invoice i left join invoiceitem ii on (COALESCE(i.extrnid,i.id) = ii.invoice_id and CASE WHEN i.extrnid is null THEN ii.extrnid is null ELSE ii.extrnid is not null END and i.store_id = ii.store_id) where vendor_id=? and i.store_id=? group by i.id order by i.number desc', array($this->args[0],$storeId));
                $returnArray['data'] = $rows;
    			return $this->handleRefreshToken($returnArray);
    		} else {
    			$vendors  = R::findOne('vendor', ' id = ? ', array($this->args[0]) );
    			return $this->handleRefreshToken(json_decode($vendors->__toString()));
    		}
    	} elseif($this->method == 'PUT'){
            if(!isset($_GET['store'])) {
                return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
            }
            $storeId = $_GET['store'];
            if(!$this->isUserAuthorizedForStore($storeId)) {
                return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
            }

    		$body = json_decode($this->file);
    		if(!isset($body->vendor_id)) {
    			return "Missing parameter: vendor_id";
    		}

            $vendor_id = $body->vendor_id;
            $column = "extrnid";
            if (strpos($vendor_id, 'C') !== false) {
                $vendor_id = str_replace('C', '', $vendor_id);
                $column = "id";
            }
    
    		$v = new stdClass();
    		$v->vendorname = $body->vendorname;
    		$v->firstname = $body->firstname;
    		$v->lastname = $body->lastname;
    		$v->phone1 = $body->phone1;
    		$v->phone2 = $body->phone2;
            $v->email = $body->email;
    		$v->address1 = $body->address1;
    		$v->address2 = $body->address2;
    		$v->zip = $body->zip;
    		$v->city = $body->city;
    		$v->state = $body->state;
            $v->active = $body->active;
    		$v->is_updated = true;
    		
            $this->db->where($column, $vendor_id);
            $this->db->where('store_id', $storeId);
    		$id = $this->db->update('vendor', get_object_vars($v));
    		return $this->handleRefreshToken(array("id"=>$id));
    	} elseif($this->method == 'DELETE'){
    		$itemtype  = R::findOne('itemtype', ' endpoint = ? ', array( $this->verb ) );
    		R::trash($itemtype);
    		return $this->handleRefreshToken(array("success" => true));
    	}
    }
    
    protected function inventory() {
    	if($this->user == null) {
    		return array("data" => array("error" => "User session timed out"), "statuscode" => 401);
    	}
    	if($this->method == 'POST') {
    		if($this->verb == "item") {
                if($this->user->isAdmin != 1) {
                    return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
                }
                $body = @file_get_contents('php://input');
                $body = json_decode($body);
                $inventory = new stdClass();
                $inventory->manufacturer = $body->manufacturer;
                $inventory->partnumber = $body->partnumber;
                $inventory->description = $body->description;
                $inventory->cost = $body->cost;
                $inventory->retail = $body->retail;
                $inventory->quantity = $body->quantity;
                $inventory->reserved = $body->reserved;
                $inventory->store_id = $body->store;
                $inventory->store_ids = implode(',',$body->storeID);
                
                $id = $this->db->insert('inventory', get_object_vars($inventory));
                return $this->handleRefreshToken(array("id"=>$id));
            } else {
                $body = @file_get_contents('php://input');
                $body = json_decode($body);
                $invoice = new stdClass();
                $invoice->number = $body->invoice;
                $invoice->vendor_id = $body->vendor_id;
                $invoice->created = date('Y-m-d G:i:s');
                $invoice->paid = 0;
                $invoice->store_id = $body->store;
                
                $id = $this->db->insert('invoice', get_object_vars($invoice));
                
                foreach($body->items as $k => $i) {
                    $item = new stdClass();
                    $item->invoice_id = $id;
                    $item->inventory_id = $i->partnumber;
                    $item->quantity = $i->quantity;
                    $item->cost = $i->cost;
                    $item->store_id = $body->store;
                    $this->db->insert('invoiceitem', get_object_vars($item));
                    
                    $this->db->objectBuilder();
                    $iItem = $this->db->rawQueryOne('select * from inventory where COALESCE(extrnid, id) = ? and store_id = ?', array($i->partnumber, $body->store));
                    $itemPartNumber = $iItem->partnumber;
                    $itemOldCost = $iItem->cost;
                    $totalItemCost = ($iItem->cost * $iItem->quantity) + ($item->cost * $item->quantity);
                    $totalItemCount = $iItem->quantity + $item->quantity;
                    $itemAverage = $totalItemCost / $totalItemCount;
                    $iItem->cost = $itemAverage;
                    $iItem->quantity = $totalItemCount;
                    if ($iItem->extrnid) {
                        $iItem->is_updated = true;
                    }
                    $this->db->where('id', $iItem->id);
                    $this->db->update('inventory', get_object_vars($iItem));
                    
                    $orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
                    $this->db->objectBuilder();
                    $oItems = $this->db->rawQuery("select oi.* from orderitem oi left join `order` o on (oi.order_id=$orderColumn and oi.store_id=o.store_id) where o.type='W' and oi.partnumber=? and oi.cost=?",array($itemPartNumber,$itemOldCost));
                    
                    foreach($oItems as $oItem) {
                        $oItem->cost = $itemAverage;
                        $this->db->where('id', $oItem->id);
                        $this->db->update('orderitem', get_object_vars($oItem));
                    }
                }
            
                return $this->handleRefreshToken(array("id"=>$id));
            }
    	} elseif($this->method == 'GET'){
    		if(!isset($_GET['store'])) {
    			return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
    		}
    		$storeId = $_GET['store'];
    		if(!$this->isUserAuthorizedForStore($storeId)) {
    		    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
    		}
    		if($this->verb == "all") {
    			$inventory = $this->db->rawQuery('select * from inventory where store_id=? order by partnumber', array($storeId));
    
    			if($inventory == null) {return array();}
    
    			$retArray = array();
    			$columns = array();
    			$columns[] = array("data"=>"manufacturer","label"=>"Manufacturer");
    			$columns[] = array("data"=>"partnumber","label"=>"Part Number");
    			$columns[] = array("data"=>"description","label"=>"Description");
    			$columns[] = array("data"=>"cost","label"=>"Cost");
    			$columns[] = array("data"=>"retail","label"=>"Retail");
    			$columns[] = array("data"=>"quantity","label"=>"On Hand");
    			$columns[] = array("data"=>"reserved","label"=>"Reserved");
    			$locations = array();
    
    			$allLocations = null;
    			if(isset($_GET['allLocations']) && $_GET['allLocations'] == "true") {
    				//$allLocations = json_decode('{"413517329":[{"identifier":"Hartville","quantity":1},{"identifier":"Mentor","quantity":6}]}',true);
    			}
    
    			foreach($inventory as $i) {
    				if($allLocations != null) {
    					$localI = json_decode($i->__toString(),true);
    					if(array_key_exists($localI['partnumber'],$allLocations)) {
    						foreach($allLocations[$localI['partnumber']] as $ai) {
    							if(!array_key_exists($ai['identifier'],$locations)) {
    								$locations[$ai['identifier']] = 1;
    							}
    							$localI[$ai['identifier']] = $ai['quantity'];
    						}
    					}
    					$retArray[] = $localI;
    				} else {
    					$retArray[] = $i;
    				}
    			}
    
    			if(isset($_GET['format']) && $_GET['format'] == "datatable") {
    				foreach($locations as $k=>$v) {
    					$columns[] = array("data"=>$k, "defaultContent"=>0, "label"=>$k);
    				}

                    if($this->user->isAdmin == 1) {
                        $columns[] = array("label"=>"Edit", "sortable" => false);
                    }
    				return $this->handleRefreshToken(array("columns"=>$columns, "data" => $retArray));
    			}
    			return $retArray;
    		} elseif($this->verb == "vendorpartnum") {
    			$vendors = $this->db->rawQuery('select COALESCE(extrnid, id) as id, vendorname from vendor where store_id = ? and active=1', array($storeId));
    			$partnum = $this->db->rawQuery('SELECT id, partnumber, description from inventory where store_id = ? order by partnumber asc', array($storeId));
    			$retArray = array("vendors" => $vendors, "partnumbers" => $partnum);
    			return $this->handleRefreshToken($retArray);
    		} elseif($this->verb == "storepartnum") {
				$stores = $this->db->rawQuery("select s.id, s.identifier from user_store us left join store s on s.id = us.store_id where user_id = ? order by s.identifier ASC", array($this->user->userId));
				$retArray = array("stores" => $stores);
				return $this->handleRefreshToken($retArray);
			} elseif($this->verb == "partlist") {
    			$rows = $this->db->rawQuery("select i.id, i.manufacturer, i.partnumber, i.description, i.cost, i.retail, iv.vendor_id, max(iv.created) as created, (i.quantity-i.reserved) as stock from inventory i left join invoiceitem ii on i.id=ii.inventory_id left join invoice iv on ii.invoice_id=iv.id where i.store_id=? group by i.id order by partnumber", array($storeId));
    			if($rows == null) {return array();}
    			if(isset($_GET['format']) && $_GET['format'] == "datatable") {
    				return array("data" => $rows);
    			}
    			return $this->handleRefreshToken($rows);
    		} elseif($this->verb == "filter") {
    			$partnums = $this->db->rawQuery( 'SELECT COALESCE(extrnid, id) as id, partnumber, description from inventory where partnumber like ? and store_id = ?', array('%' . $this->args[0] . '%', $storeId));
    			return $this->handleRefreshToken(array('partnums' => $partnums));
    		} elseif($this->verb == "history") {
    			$returnArray = array();
    			$column = "extrnid";
    			if (strpos($this->args[0], 'C') !== false) {     
                    $column = "id";        
                    $this->args[0] = str_replace('C', '', $this->args[0]);     
                } 
    			$returnArray['detail'] = $this->db->rawQuery('select inv.number, DATE_FORMAT(inv.created, "%m/%d/%Y") as date, inv.paid, v.vendorname from invoice inv left join vendor v on (inv.vendor_id = COALESCE(v.extrnid, v.id) and inv.store_id=v.store_id) where inv.'.$column.'=? and inv.store_id=?', array($this->args[0],$storeId));
    			$returnArray['items'] =$this->db->rawQuery('SELECT COALESCE(ivt.partnumber,oi.partnumber) as partnumber, COALESCE(ivt.description, oi.description) as description, ii.quantity, ii.cost, (ii.quantity*ii.cost) as total FROM `invoiceitem` ii left join inventory ivt on (ii.inventory_id=COALESCE(ivt.extrnid, ivt.id) and ii.store_id=ivt.store_id) left join invoice i on (ii.invoice_id=i.'.$column.' and ii.store_id=i.store_id) left join orderitem oi on (i.number=oi.invoicenumber and i.vendor_id=oi.vendor_id and i.store_id=oi.store_id) where invoice_id=? and ii.store_id=?', array($this->args[0],$storeId));
    			return $this->handleRefreshToken($returnArray);
    		} else {
    			$inventory  = R::findOne('inventory', ' id = ? ', array($this->args[0]) );
    			return $this->handleRefreshToken(json_decode($inventory->__toString()));
    		}
    	} elseif($this->method == 'PUT'){
    		$body = json_decode($this->file);
            if(!isset($body->inventory_id)) {
                return "Missing parameter: inventory_id";
            }
            if($this->verb == "item") {
                if($this->user->isAdmin != 1) {
                    return Array("data" => array("error" => "User does not have required permissions to execute method"), "statuscode" => 405);
                }
                $i = new stdClass();
                $i->manufacturer = $body->manufacturer;
                $i->partnumber = $body->partnumber;
                $i->description = $body->description;
                $i->cost = $body->cost;
                $i->retail = $body->retail;
                $i->quantity = $body->quantity;
                $i->reserved = $body->reserved;
                $i->is_updated = true;
                if(!empty($body->storeID)) {
                    $i->store_ids = implode(',', $body->storeID);
                }
                
                $this->db->where('id', $body->inventory_id);
                $id = $this->db->update('inventory', get_object_vars($i));
                return $this->handleRefreshToken(array("id"=>$id));
            }
    	} elseif($this->method == 'DELETE'){
    		$itemtype  = R::findOne('itemtype', ' endpoint = ? ', array( $this->verb ) );
    		R::trash($itemtype);
    		return $this->handleRefreshToken(array("success" => true));
    	}
    }

    protected function template() {
        if($this->user == null) {
            return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
        }
        if($this->method == 'POST') {
            $template = new stdClass();
            $body = @file_get_contents('php://input');
            $body = json_decode($body);

            if(isset($body->templatename) && $body->templatename != "") {
                $template->name = $body->templatename;
            } else {
                return array("data"=>array("error" => "Invalid name"), "statuscode" => 400);
            }
            
            $templateExists = $this->db->rawQueryOne('select * from template where name = ? and store_id = ?', array($body->templatename, $body->store));
            if($templateExists != null) {
                return array("error" => "A template exists with that name.");
            }
            
            $template->store_id = $body->store;
            $id = $this->db->insert('template', get_object_vars($template));
            
            foreach($body->items as $k => $i) {
                $item = new stdClass();
                $item->template_id = $id;
                $item->itemtype_id = $i->type;
                $item->partnumber = $i->partnumber;
                $item->description = $i->description;
                $item->quantity = $i->quantity;
                $item->retail = $i->retail;
                $item->cost = $i->cost;
                $item->taxcat = $i->taxcat;
                $item->store_id = $body->store;
                if(isset($i->dotnumber) && $i->dotnumber != "") {
                    $item->dotnumber = $i->dotnumber;
                }
                
                if(isset($i->vendor_id) && $i->vendor_id != "") {
                    $item->vendor_id = $i->vendor_id;
                }
                
                if(isset($i->invoicenumber) && $i->invoicenumber != "") {
                    $item->invoicenumber = $i->invoicenumber;
                }
                
                if(isset($i->tax) && $i->tax != "") {
                    $item->tax = $i->tax;
                }
                
                $this->db->insert('templateitem', get_object_vars($item));
            }

            return $this->handleRefreshToken(array("id"=>$id));
        } elseif($this->method == 'GET'){
            if(!isset($_GET['store'])) {
                return "Missing parameter: store";
            }
            $storeId = $_GET['store'];
            if(!$this->isUserAuthorizedForStore($storeId)) {
                return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
            }

            if($this->verb == "filter") {
                $templates = $this->db->rawQuery( 'SELECT COALESCE(extrnid, id) as id, name from template where name like ? and store_id = ?', array('%' . $this->args[0] . '%', $storeId));
                return $templates;
            } else {
                $template = $this->db->rawQueryOne('select * from template where COALESCE(extrnid, id) = ? and store_id = ?', array($this->args[0], $storeId));
                $returnArray = $template;
                $templateItems = $this->db->rawQuery('select * from templateitem where template_id = ? and store_id = ?', array($this->args[0], $storeId));
                foreach($templateItems as $ti) {
                    $returnArray['items'][] = $ti;
                }
                return $this->handleRefreshToken($returnArray);
            }
        } elseif($this->method == 'PUT'){
        } elseif($this->method == 'DELETE'){
            $id = $this->args[0];
            $this->db->objectBuilder();
            $template = $this->db->where('COALESCE(extrnid, id)', $id)->where('store_id', $_GET['store'])->getOne('template');
            // Inserting deleted template/items into deleted_data table for syncing
            if ($template->extrnid) {
                $row = array(
                    'tbl' => 'template',
                    'delete_column' => 'id',
                    'delete_value' => $template->extrnid,
                    'store_id' => $_GET['store'],
                );
                $this->db->insert('deleted_data', $row);
            }

            $this->db->where('template_id', $id)->where('store_id', $_GET['store'])->delete('templateitem');
            $this->db->where('COALESCE(extrnid, id)', $id)->delete('template');
            return $this->handleRefreshToken(array("status" => "success"));
        }
    }
    
    protected function location() {
    	if($this->method == 'POST') {
    	} elseif($this->method == 'GET'){
    		$storeId = $_GET['store'];
    		if($this->verb == "inventory") {
    			$inventory = $this->db->rawQuery('select i.partnumber, i.manufacturer, i.description, (i.quantity-i.reserved) as stock, s.identifier from inventory i left join store s on (i.store_id = s.id and s.organization_id=(select organization_id from store where id=?)) where s.id != ?', array($storeId, $storeId));
    
    			if($inventory == null) {return array();}
    
    			$retArray = array();
    
    			foreach($inventory as $i) {
    				if(!array_key_exists($i['partnumber'],$retArray)) {
    					$retArray[$i['partnumber']] = array();
    				}
   					$retArray[$i['partnumber']][] = array(
                        "identifier" => $i['identifier'],
                        "manufacturer" => $i['manufacturer'],
                        "description" => $i['description'],
                        "quantity" => $i['stock']
                    );
    			}
    
    			return $retArray;
    		}
    	} elseif($this->method == 'PUT'){
    	} elseif($this->method == 'DELETE'){
    	}
    }
    
	protected function sync() {
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body, true);

			$store = $this->db->rawQueryOne('SELECT id FROM `store` where id=? and secret_key=?', array($body['data']['store_id'], $body['store_key']));
			if(!$store) {
				return ['data' => ['error' => 'Unauthorize'], 'statuscode' => 401];
			}

			try {
				if(isset($body['deletePrevious']) && $body['deletePrevious'] == 'true') {
					$this->db->where($body['delete_column'], $body['delete_value']);
					$this->db->where('store_id', $body['data']['store_id']);
					$id = $this->db->delete($body['table']);
				}
				if(!isset($body['null_body']) || $body['null_body'] != 'true') {
					if(isset($body['data']['id'])) {
                        if ($body['table'] == 'teammember') {
                            $this->db->where("employee_id", $body['data']['employee_id']);
                        } elseif ($body['table'] == 'order_teammember') {
                            $this->db->where("order_id", $body['data']['order_id']);
                            $this->db->where("teammember_id", $body['data']['teammember_id']);
                        } else {
                            $body['data']['extrnid'] = $body['data']['id'];
                            $this->db->where("extrnid", $body['data']['id']);
                        }

						$this->db->where('store_id', $body['data']['store_id']);
						$row = $this->db->get($body['table']);
						
						if($this->db->count < 1) {
                            unset($body['data']['id']);
							$id = $this->db->insert($body['table'], $body['data']);
						}
					} else {
						$id = $this->db->insert($body['table'], $body['data']);
					}
				}
			} catch (Exception $e) {
				throw $e;
			}
			
			if($id) {
				return $id;
			}
			return array("data" => array("error"=>$this->db->getLastError(),"query" => $this->db->getLastQuery()), "statuscode"=>500);
		} elseif($this->method == 'GET'){
		} elseif($this->method == 'PUT'){
			$body = json_decode($this->file, true);

			$store = $this->db->rawQueryOne('SELECT id FROM `store` where id=? and secret_key=?', array($body['data']['store_id'], $body['store_key']));
			if(!$store) {
				return ['data' => ['error' => 'Unauthorize'], 'statuscode' => 401];
			}
			
			if(!isset($body['update_id'])) {
				throw new Exception("Missing parameter: update_id");
			}

			try {
                if ($body['table'] == 'store') {
                    $this->db->where('id', $body['update_id']);
                    unset($body['data']['store_id']);
                } elseif ($body['table'] == 'teammember') {
                    $this->db->where('employee_id', $body['update_id']);
                    $this->db->where('store_id', $body['data']['store_id']);
                } else {
                    if (isset($body['twoWaySync']) && $body['twoWaySync'] == true) {
                        $this->db->where('id', $body['update_id']);
                        $body['data']['extrnid'] = $body['data']['id'];
                    } else {
                        $this->db->where('extrnid', $body['update_id']);
                    }

                    $this->db->where('store_id', $body['data']['store_id']);
                }
				
                unset($body['data']['id']);
                unset($body['data']['central_id']);
				$id = $this->db->update($body['table'], $body['data']);
			} catch (Exception $e) {
				throw $e;
			}
				
			if($id) {
				return $id;
			}
			return array("data" => array("error"=>$this->db->getLastError(),"query" => $this->db->getLastQuery()), "statuscode"=>500);
		} elseif($this->method == 'DELETE'){
            $body = @file_get_contents('php://input');
            $body = json_decode($body, true);
            if(!isset($body['update_id'])) {
                throw new Exception("Missing parameter: deleted_id");
            }

            $this->db->where('id', $body['update_id'])->delete('deleted_data');
		}	
	}
	
	protected function resync() {
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body, true);

			$store = $this->db->rawQueryOne('SELECT id FROM `store` where id=? and secret_key=?', array($body['data']['store_id'], $body['store_key']));
			if(!$store) {
				return ['data' => ['error' => 'Unauthorize'], 'statuscode' => 401];
			}
	
			$id = -1;
			try {
				if ($body['table'] == 'store') {
					$this->db->where('id', $body['key']);
					unset($body['data']['store_id']);
				} elseif ($body['table'] == 'teammember') {
					$this->db->where("employee_id", $body['data']['employee_id']);
				} elseif ($body['table'] == 'order_teammember') {
					$this->db->where("order_id", $body['data']['order_id']);
					$this->db->where("teammember_id", $body['data']['teammember_id']);
				} else {
					$body['data']['extrnid'] = $body['key'];
					$this->db->where("extrnid", $body['key']);
				}

				if ($body['table'] != 'store') {
					$this->db->where('store_id', $body['data']['store_id']);
				}
				
				$record = $this->db->getOne($body['table']);
				unset($body['data']['id']);
				unset($body['data']['central_id']);
				
				if($this->db->count < 1) {
					$id = $this->db->insert($body['table'], $body['data']);
				} else {
					$this->db->where("id", $record['id']);
					$id = $this->db->update($body['table'], $body['data']);
				}
				
				if($body['table'] == 'order') {
				    $this->db->where("order_id", $body['key']);
				    $this->db->where('store_id', $body['data']['store_id']);
				    $id = $this->db->delete("orderitem");
				    
				    $this->db->where("order_id", $body['key']);
				    $this->db->where('store_id', $body['data']['store_id']);
				    $id = $this->db->delete("orderpayinfo");
				    
				    $this->db->where("order_id", $body['key']);
				    $this->db->where('store_id', $body['data']['store_id']);
				    $id = $this->db->delete("order_teammember");
				}
	
			} catch (Exception $e) {
				throw $e;
			}
				
			return $id;
		} elseif($this->method == 'GET'){
		} elseif($this->method == 'PUT'){
		} elseif($this->method == 'DELETE'){
		}
	}
	
	protected function user() {
		if($this->method == 'POST') {
			$body = @file_get_contents('php://input');
			$body = json_decode($body, true);
	
			if($this->verb == "authenticate") {
				if(!isset($body['username']) || $body['username'] == "") {
					throw new Exception("Email is required");
				}
				if(!isset($body['password']) || $body['password'] == "") {
					throw new Exception("Password is required");
				}
				
				$this->db->where('email', $body['username']);
				$usr = $this->db->getOne("user");
				
				if($usr == null) {
					throw new Exception("Invalid email or password");
				}
				
				//$p = password_hash("passw0rd", PASSWORD_BCRYPT);
				if(!password_verify($body["password"], $usr["password"])) {
					throw new Exception("Invalid email or password");
				}
				
				$tokenId = base64_encode(random_bytes(32));
				$issuedAt = time();
				$notBefore = $issuedAt;
				$expire = $notBefore + SESSION_TIME_SECONDS;
				$serverName = DOMAIN;
				
				$usersession = array("tokenid" => $tokenId, "created" => $this->db->now(), "ipaddress" => $_SERVER['REMOTE_ADDR'], "lastused" => $this->db->now());

				$data = [
					'iat'  => $issuedAt,
					'jti'  => $tokenId,
					'iss'  => $serverName,
					'nbf'  => $notBefore,
					'exp'  => $expire,
					'data' => [
						'userId'   => $usr["id"],
						'email' => $usr["email"],
						'orgId' => $usr["organization_id"],
						'isAdmin' => $usr["isadmin"]
					]
				];

				$jwt = JWT::encode($data, SECRET_KEY, ALGO);
				
				$this->db->insert('usersession', $usersession);

				return array('token' => $jwt, 'ur' => $usr["isadmin"]);
			}
		} elseif($this->method == 'GET'){
			if($this->user == null) {
				return array("data"=>array("error" => "Expired or Invalid Token"),"statuscode"=>401);
			}
			if($this->verb == "stores") {
				$returnArray = array();
				$returnArray['stores'] = $this->db->rawQuery('select id, identifier from store s left join user_store us on (s.id=us.store_id) where us.user_id=? order by identifier ASC', array($this->user->userId));
				if($this->refreshedToken != null) {
					$returnArray['refreshToken'] = $this->refreshedToken;
				}
				return $returnArray;
			}
            else {
                $this->user->loggedIn = true;
                return $this->user;
            }
		} elseif($this->method == 'PUT'){
		} elseif($this->method == 'DELETE'){
			if($this->verb == "authenticate") {
				if($this->user == null || $this->token == null) {
					return array('success' => true);
				}
			
				$this->db->where('tokenid', $this->token);
        		$this->db->where('ipaddress', $_SERVER['REMOTE_ADDR']);
        		$this->db->delete('usersession');
			
				return array('success' => true);
			}
		}
	}
	
	protected function ping() {
		if($this->method == 'GET'){
			if(isset($_GET['identifier'])) {
				$this->db->insert ('ping', array("identifier"=>$_GET['identifier'], "ip"=>$_SERVER['REMOTE_ADDR'], "hit"=>$this->db->now()));
			}
		}
	}
	
	protected function report() {
		if($this->user == null) {
			return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
		}
		if($this->method == 'GET'){
			if($this->verb == "postedtotals") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}

                $contactColumn = "CASE WHEN o.extrnid is null THEN co.id ELSE co.extrnid END"; 
                $customerColumn = "CASE WHEN o.extrnid is null THEN cu.id ELSE cu.extrnid END"; 
                $orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
                $pmethodColumn = "CASE WHEN pm.extrnid is null THEN pm.id ELSE pm.extrnid END";
				$orders = $this->db->rawQuery("select o.ordertax,  DATE_FORMAT(o.updated, '%m/%d/%Y') as date, COALESCE(o.extrnid,concat('C',o.id)) as id, cu.businessname, co.firstname, co.lastname, opi.amount, pm.name, cu.internal from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) left join customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) left join orderpayinfo opi on ($orderColumn = opi.order_id and o.store_id=opi.store_id) left join paymentmethod pm on (opi.paymentmethod_id=$pmethodColumn and opi.store_id=pm.store_id) where o.type='I' AND o.updated >= ? AND o.updated <= ? and o.store_id=?", array($start,$end,$storeId));

				$returnArray = array();
				$paymentTotals = array();
				$chartData = array();
				$chartData['labels'] = array();
				$chartData['datasets'] = array();
				$chartData['datasets'][] = array();
				$chartData['datasets'][0]['label'] = "";
				$chartData['datasets'][0]['fill'] = "false";
				$chartData['datasets'][0]['borderWidth'] = 1;
				$chartData['datasets'][0]['data'] = array();
				$chartData['datasets'][0]['backgroundColor'] = array();
				$chartData['datasets'][0]['borderColor'] = array();
				$grandTotal = 0;
				foreach($orders as $o) {
					if(!array_key_exists($o['name'],$paymentTotals)) {
						$paymentTotals[$o['name']] = 0;
					}
					if($o['internal'] != "1") {
						$paymentTotals[$o['name']] = $paymentTotals[$o['name']] + $o['amount'];
						$grandTotal += $o['amount'];
					}
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray['summary'][$o['name']]['invoices'][] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount'], "internal" => $o['internal']);
				}
	
				foreach($paymentTotals as $p => $a) {
					$returnArray['totals']['paymeth'][] = array("name" => $p, "amount" => $a);
					$returnArray['summary'][$p]['total'] = $a;
					$chartData['datasets'][0]['data'][] = $a;
					$chartData['datasets'][0]['backgroundColor'][] = "rgba(0, 105, 177, 1)";
					$chartData['datasets'][0]['borderColor'][] = "rgba(0, 105, 177, 1)";
					$chartData['labels'][] = $p;
				}
				$returnArray['totals']['grand'] = $grandTotal;
                
                $itemtypeColumn = "CASE WHEN o.extrnid is null THEN it.id ELSE it.extrnid END";
				$oitems = $this->db->rawQuery("SELECT oi.quantity, oi.retail, oi.tax, oi.taxcat, CASE WHEN oi.cost IS NULL OR oi.cost = '' THEN 0 ELSE oi.cost END AS cost, it.category, it.dotrequired, cu.taxexempt, tr.exemption FROM `order` o LEFT JOIN orderitem oi ON ( $orderColumn = oi.order_id AND o.store_id = oi.store_id) LEFT JOIN itemtype it ON ( oi.itemtype_id = $itemtypeColumn  AND oi.store_id = it.store_id) LEFT JOIN taxrate tr on (it.category = tr.category AND it.store_id = tr.store_id) LEFT JOIN contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) LEFT JOIN customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) WHERE o.type = 'I' AND (cu.internal = 0 OR cu.internal is null) AND o.updated >=  ? AND o.updated <=  ? and oi.store_id=?", array($start,$end,$storeId));

				$partCost = 0;
				$tireCost = 0;
				$partSales = 0;
				$tireSales = 0;
				$taxes = array();
				foreach($oitems as $oi) {
					if(!array_key_exists($oi['category'],$taxes)) {
						$taxes[$oi['category']] = 0;
					}
					if($oi['tax'] != "") {
						$taxes[$oi['category']] = $taxes[$oi['category']] + $oi['tax'];
					} else {
						if($oi['taxexempt'] == 1) {
							if($oi['exemption'] == "") {
								$oi['taxrate'] = 0;
							} else {
								$oi['taxcat'] = $oi['taxcat'] - ($oi['taxcat'] * ($oi['exemption']/100));
							}
						}
						$taxes[$oi['category']] = $taxes[$oi['category']] + ($oi['quantity'] * $oi['retail'] * ($oi['taxcat']/100));
					}
					if(!array_key_exists($oi['category'] . "sales",$returnArray['totals'])) {
						$returnArray['totals'][$oi['category'] . "sales"] = 0;
					}
					$returnArray['totals'][$oi['category'] . "sales"] += ($oi['quantity'] * $oi['retail']);
					if($oi['dotrequired'] == 1) {
						$tireCost += ($oi['quantity'] * $oi['cost']);
					} else {
						$partCost += ($oi['quantity'] * $oi['cost']);
					}
					
					if($oi['category'] == "part") {
						if($oi['dotrequired'] == 1) {
							$tireSales += ($oi['quantity'] * $oi['retail']);
						} else {
							$partSales += ($oi['quantity'] * $oi['retail']);
						}
					}
				}
				
				$returnArray['totals']['tirecost'] = $tireCost;
				$returnArray['totals']['partcost'] = $partCost;
				$returnArray['totals']['tiresales'] = $tireSales;
				$returnArray['totals']['partsales'] = $partSales;
				
				foreach($taxes as $c => $a) {
					$returnArray['totals']['taxes'][] = array("name" => $c, "amount" => round($a,2));
				}

				$pmId = 'CASE WHEN pm.extrnid is null THEN pm.id ELSE pm.extrnid END';
				$arpayments = $this->db->rawQuery("select sum(opi.amount) as amount, pm.name from orderpayinfo opi left join paymentmethod pm on (opi.closedmethod=$pmId and opi.store_id=pm.store_id) where paymentmethod_id != closedmethod and opi.paydate >= ? and opi.paydate <= ? and opi.store_id = ? group by opi.closedmethod order by name asc", array($start,$end, $storeId));
				$returnArray['arpayments'] = $arpayments;
				
				$totalInvoices = $this->db->rawQueryValue("select count(o.id) from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) left join customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) where o.type='I' AND (cu.internal = 0 OR cu.internal is null) AND o.updated >= ? AND o.updated <= ? AND o.store_id=? limit 1", array($start,$end,$storeId));
				if($totalInvoices > 0) {
					$returnArray['totals']['average'] = round($grandTotal/$totalInvoices,2);
				} else {
					$returnArray['totals']['average'] = 0;
				}
				$returnArray['chartjs'] = $chartData;
				$returnArray['totals']['invoicecount'] = $totalInvoices;
				
				return $this->handleRefreshToken($returnArray);
			} elseif($this->verb == "accountsreceivable") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}

                $contactColumn = "CASE WHEN o.extrnid is null THEN co.id ELSE co.extrnid END";
                $customerColumn = "CASE WHEN o.extrnid is null THEN cu.id ELSE cu.extrnid END";
                $paymentmethodColumn = "CASE WHEN o.extrnid is null THEN pm.id ELSE pm.extrnid END";
                $vehicleColumn = "CASE WHEN o.extrnid is null THEN v.id ELSE v.extrnid END";
				$orders = $this->db->rawQuery("select o.ordertax, DATE_FORMAT(o.updated, '%m/%d/%Y') as date, COALESCE(o.extrnid,concat('C',o.id)) as id, opi.id as opiid, cu.businessname, co.firstname, co.lastname, cu.addressline1, cu.addressline2, cu.addressline3, cu.city, cu.state, cu.zip, co.phone1, opi.amount, pm.name, v.fleetnum from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) left join customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) left join orderpayinfo opi on (COALESCE(o.extrnid, o.id) = opi.order_id and o.store_id = opi.store_id) left join paymentmethod pm on (opi.paymentmethod_id = $paymentmethodColumn and opi.store_id = pm.store_id) left join vehicle v on (o.vehicle_id = $vehicleColumn and o.store_id = v.store_id) where o.type='I' and opi.paydate is null and opi.amount is not null and o.updated >= ? and o.updated <= ? and o.store_id = ? order by cu.id asc, o.id asc", array($start,$end,$storeId));
				$returnArray = array();
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$fleetnum = $o['fleetnum'];
					if($fleetnum == null) {
						$fleetnum = "";
					}
					$returnArray["accounts"][$name]["orders"][] = array("date" => $o['date'], "number" => $o['id'], "orderpayinfoid" => $o['opiid'], "name" => $name, "amount" => $o['amount'], "fleetnum" => $fleetnum);
					if(!array_key_exists("details",$returnArray["accounts"][$name])) {
						$returnArray["accounts"][$name]["details"] = array("total" => 0, "name" => $name, "addressline1" => $o['addressline1'], "addressline2" => $o['addressline2'], "addressline3" => $o['addressline3'], "city" => $o['city'], "state" => $o['state'], "zip" => $o['zip'], "phone1" => $o['phone1']);
					}

					$returnArray["accounts"][$name]["details"]["total"] = $returnArray["accounts"][$name]["details"]["total"] + $o['amount'];
				}
				
				$paymentmethods = $this->db->rawQuery('select *, COALESCE(extrnid, id) as id from paymentmethod where store_id = ? and active=1 and open=0', array($storeId) );
				$returnArray["paymentmethods"] = $paymentmethods;
				
				
				return $this->handleRefreshToken($returnArray);
			} elseif($this->verb == "accountspayable") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}

				$invoices = $this->db->rawQuery("select COALESCE(i.extrnid, concat('C',i.id)) as id, i.number, DATE_FORMAT(i.created, '%m/%d/%Y') as date, v.vendorname from invoice i left join vendor v on (i.vendor_id = COALESCE(v.extrnid, v.id) and i.store_id = v.store_id) where i.paid = 0 and i.created >= ? and i.created <= ? and i.store_id = ? order by v.vendorname asc, i.created asc", array($start,$end,$storeId));
				$returnArray['data'] = array();
				foreach($invoices as $i) {
                    $invoice_id = $i['id'];
                    if (strpos($i['id'], 'C') !== false) {     
                        $invoice_id = str_replace('C', '', $i['id']);     
                    } 
					$iTotal = $this->db->rawQueryValue("select sum(quantity * cost) from invoiceitem where invoice_id=? and store_id=? limit 1", array($invoice_id,$storeId));
					if (is_numeric($iTotal)) {
                        $returnArray['data'][$i['vendorname']][] = array("date" => $i['date'], "number" => $i['number'], "id" => $i['id'], "amount" => number_format($iTotal,2));
                    }
				}
				return $this->handleRefreshToken($returnArray);
			} elseif($this->verb == "lowmargin") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}

				$contactColumn = "CASE WHEN co.extrnid is null THEN co.id ELSE co.extrnid END";	
				$customerColumn = "CASE WHEN cu.extrnid is null THEN cu.id ELSE cu.extrnid END";
				$orderColumn = "CASE WHEN o.extrnid is null THEN o.id ELSE o.extrnid END";
				$paymentMethodColumn = "CASE WHEN pm.extrnid is null THEN pm.id ELSE pm.extrnid END";
				$orders = $this->db->rawQuery("select o.ordermargin,  DATE_FORMAT(o.updated, '%m/%d/%Y') as date, COALESCE(o.extrnid,concat('C',o.id)) as id, cu.businessname, co.firstname, co.lastname, opi.amount, pm.name from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) left join customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) left join orderpayinfo opi on ($orderColumn = opi.order_id and o.store_id = opi.store_id) left join paymentmethod pm on (opi.paymentmethod_id=$paymentMethodColumn and opi.store_id = pm.store_id) where o.type='I' and o.ordermargin < 30 and o.updated >= ? and o.updated <= ? and o.store_id = ? order by ordermargin asc", array($start,$end,$storeId));
				
				$returnArray = array();
				$returnArray['data'] = [];
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray['data'][] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount'], "margin" => $o['ordermargin']);
				}
				return $this->handleRefreshToken($returnArray);
			} elseif($this->verb == "salestaxexempt") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}

				$contactColumn = "CASE WHEN co.extrnid is null THEN co.id ELSE co.extrnid END";
				$customerColumn = "CASE WHEN cu.extrnid is null THEN cu.id ELSE cu.extrnid END";
				$orders = $this->db->rawQuery("select DATE_FORMAT(o.updated, '%m/%d/%Y') as date, COALESCE(o.extrnid,concat('C',o.id)) as id, o.ordertotal, cu.businessname, co.firstname, co.lastname, cu.taxexemptnum from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) left join customer cu on (co.customer_id = $customerColumn and co.store_id = cu.store_id) where o.ordertotal > 0 and o.ordertax < 0.01 and o.type='I' AND o.updated >= ? AND o.updated <= ? AND o.store_id = ?", array($start,$end,$storeId));
				$returnArray = array();
				$returnArray['data'] = [];
				foreach($orders as $o) {
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray['data'][] = array("date" => $o['date'], "id" => $o['id'], "name" => $name, "amount" => $o['ordertotal'], "taxexemptnum" => $o['taxexemptnum']);
				}
				return $this->handleRefreshToken($returnArray);
			} elseif($this->verb == "techproductivity") {
				if(!isset($_GET['start'])) {
					return "Missing parameter: start";
				}
				if(!isset($_GET['end'])) {
					return "Missing parameter: end";
				}
				if(!isset($_GET['store'])) {
					return "Missing parameter: store";
				}
				$start = $_GET['start'] . " 00:00:00";
				$end = $_GET['end'] . " 23:59:59";
				$storeId = $_GET['store'];
				if(!$this->isUserAuthorizedForStore($storeId)) {
				    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
				}
				$orders = $this->db->rawQuery('select DATE_FORMAT(o.updated, "%m/%d/%Y") as date, o.id, cu.businessname, co.firstname, co.lastname, (o.ordertotal - o.ordertax) as amount, e.name from `order` o left join contact co on (o.contact_id = co.id and o.store_id = co.store_id) left join customer cu on (co.customer_id = cu.id and co.store_id = cu.store_id) left join order_teammember ot on (o.id=ot.order_id and o.store_id=ot.store_id) left join teammember tm on (ot.teammember_id = tm.id and ot.store_id = tm.store_id) left join employee e on (tm.employee_id = e.id) where o.type="I" and o.updated >= ? and o.updated <= ? and o.store_id = ? order by o.id asc', array($start,$end,$storeId));
			
				$returnArray = array();
				foreach($orders as $o) {
					if(!array_key_exists($o['name'],$returnArray)) {
						$returnArray[$o['name']] = array("total" => 0, "invoices" => array());
					}
					$name = $o['businessname'];
					if($name == null || $name == "") {
						$name = $o['firstname'] . " " . $o['lastname'];
					}
					$returnArray[$o['name']]['total'] += $o['amount'];
					$returnArray[$o['name']]['invoices'][] = array("date" => $o['date'], "number" => $o['id'], "name" => $name, "amount" => $o['amount']);
				}
				return $this->handleRefreshToken($returnArray);
			} elseif ($this->verb == "inventorysold") {
                if(!isset($_GET['start'])) {
                    return "Missing parameter: start";
                }
                if(!isset($_GET['end'])) {
                    return "Missing parameter: end";
                }
                if(!isset($_GET['store'])) {
                    return "Missing parameter: store";
                }
                if(!isset($_GET['invoice_number'])) {
                    return "Missing parameter: invoice number";
                }

                $start = $_GET['start'] . " 00:00:00";
                $end = $_GET['end'] . " 23:59:59";
                $storeId = $_GET['store'];
                $invoiceNumber = $_GET['invoice_number'];
                if(!$this->isUserAuthorizedForStore($storeId)) {
                    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
                }
                $rows = $this->db->rawQuery("SELECT COALESCE(o.extrnid, o.id) as id, oi.partnumber, oi.quantity, oi.description, (oi.quantity*oi.cost) as total_cost, oi.invoicenumber, DATE(o.updated) as date, (oi.quantity*oi.retail) as total_retail, (SELECT manufacturer from `inventory` ivt WHERE oi.partnumber = ivt.partnumber and oi.store_id = ivt.store_id) as manufacturer FROM `order` o LEFT JOIN orderitem oi ON (COALESCE(o.extrnid, o.id) = oi.order_id and o.store_id = oi.store_id) WHERE o.type = 'I' and oi.itemtype_id = '4' and o.updated >= ? and o.updated <= ? and o.store_id = ? and oi.invoicenumber = ?", array($start, $end, $storeId, $invoiceNumber));
                
                $columns = array();
                $returnArray = array();
                $columns[] = array("data"=>"manufacturer", "defaultContent"=>"", "label"=>"Manufacturer");
                $columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
                $columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
                $columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost");
                $columns[] = array("data"=>"total_retail", "defaultContent"=>0, "label"=>"Total Retail");
                $columns[] = array("data"=>"quantity", "defaultContent"=>0, "label"=>"Total Quantity");
                $columns[] = array("data"=>"invoicenumber", "defaultContent"=>0, "label"=>"Ivoice Number");
                $columns[] = array("data"=>"date", "defaultContent"=>0, "label"=>"Date");
                
                if(isset($_GET['format']) && $_GET['format'] == "datatable") {
                    $returnArray['inventorySold'] = array("columns"=>$columns, "data" => $rows);
                    return $this->handleRefreshToken($returnArray);
                }
                
                $returnArray['inventorySold'] = $rows;
                return $this->handleRefreshToken($returnArray);
            } elseif($this->verb == "inventoryDollars") {
                if(!isset($_GET['end'])) {
                    return "Missing parameter: end";
                }
                if(!isset($_GET['store'])) {
                    return "Missing parameter: store";
                }

                $end = $_GET['end'] . " 23:59:59";
                $storeId = $_GET['store'];
                if(!$this->isUserAuthorizedForStore($storeId)) {
                    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
                }

                $rows_inventory = $this->db->rawQuery("SELECT id, partnumber, description, cost, quantity, (cost * quantity) as rawCost from inventory");

                $rows_invoiceitem = $this->db->rawQuery("SELECT invi.invoice_id, invi.inventory_id, (sum(invi.quantity)) as total_quantity, (sum(invi.cost)) as total_cost  from invoiceitem invi left join invoice inv on inv.id = invi.invoice_id where inv.created >= ? and inv.store_id = ? group by invi.inventory_id", array($end, $storeId));

                $returnArray = array();
                $columns = array();
                $columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
                $columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
                $columns[] = array("data"=>"unit_cost", "defaultContent"=>0, "label"=>"Unit Cost");
                $columns[] = array("data"=>"total_quantity", "defaultContent"=>0, "label"=>"Quantity on Hand");
                $columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost of items");
                $unit_cost = 0;
                $total_quantity = 0;
                $total_cost = 0;

                foreach ($rows_inventory as $inventory) {
                    $unit_cost = $inventory['cost'];
                    $total_quantity = $inventory['quantity'];
                    if ($total_quantity == 0) {
                        $total_cost = $unit_cost;
                    } else {
                        $total_cost = $inventory['rawCost'];
                    }

                    foreach ($rows_invoiceitem as $invoiceitem) {
                        if ($invoiceitem['inventory_id'] == $inventory['id']) {
                            $total_quantity = $inventory['quantity'] - $invoiceitem['total_quantity'];
                            $total_cost = $inventory['rawCost'] - $invoiceitem['total_cost'];
                            if($total_quantity != 0) {
                                $unit_cost = $total_cost / $total_quantity;
                            }
                        }
                    }
                    $returnArray[] = array('id' => $inventory['id'], 'partnumber' => $inventory['partnumber'], 'description' => $inventory['description'], 'unit_cost' => round($unit_cost, 2), 'total_quantity' => $total_quantity, 'total_cost' => round($total_cost, 2));
                }

                if(isset($_GET['format']) && $_GET['format'] == "datatable") {
                    return array("columns" => $columns, "data" => $returnArray);
                }
                return $returnArray;
            } elseif($this->verb == "outsidepurchasetiressold") {
                if(!isset($_GET['start'])) {
                    return "Missing parameter: start";
                }
                if(!isset($_GET['end'])) {
                    return "Missing parameter: end";
                }
                if(!isset($_GET['store'])) {
                    return "Missing parameter: store";
                }

                $start = $_GET['start'] . " 00:00:00";
                $end = $_GET['end'] . " 23:59:59";
                $storeId = $_GET['store'];
                if(!$this->isUserAuthorizedForStore($storeId)) {
                    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
                }
                $vendorColumn = "CASE WHEN o.extrnid is null THEN vnd.id ELSE vnd.extrnid END";
                $rows = $this->db->rawQuery("SELECT COALESCE(o.extrnid, o.id) as id, oi.partnumber, oi.quantity, oi.description, (oi.quantity*oi.cost) as total_cost, (oi.quantity*oi.retail) as total_retail, (SELECT manufacturer from `inventory` ivt WHERE oi.partnumber = ivt.partnumber and oi.store_id = ivt.store_id) as manufacturer, (SELECT vendorname from `vendor` vnd WHERE oi.vendor_id = $vendorColumn and oi.store_id = vnd.store_id) as vendor FROM `order` o LEFT JOIN orderitem oi ON (COALESCE(o.extrnid, o.id) = oi.order_id and o.store_id = oi.store_id) WHERE o.type = 'I' and oi.itemtype_id = '2' and o.updated >= ? and o.updated <= ? and o.store_id = ?", array($start, $end, $storeId));
                
                $returnArray = array();
                $columns = array();
                $columns[] = array("data"=>"vendor", "defaultContent"=>"", "label"=>"Vendor");
                $columns[] = array("data"=>"manufacturer", "defaultContent"=>"", "label"=>"Manufacturer");
                $columns[] = array("data"=>"partnumber", "defaultContent"=>"", "label"=>"Part Number");
                $columns[] = array("data"=>"description", "defaultContent"=>"", "label"=>"Description");
                $columns[] = array("data"=>"total_cost", "defaultContent"=>0, "label"=>"Total Cost");
                $columns[] = array("data"=>"total_retail", "defaultContent"=>0, "label"=>"Total Retail");
                $columns[] = array("data"=>"quantity", "defaultContent"=>0, "label"=>"Total Quantity");
                
                if(isset($_GET['format']) && $_GET['format'] == "datatable") {
                    $returnArray['purchaseTiresSold'] = array("columns"=>$columns, "data" => $rows);
                    return $this->handleRefreshToken($returnArray);
                }
                
                $returnArray['purchaseTiresSold'] = $rows;
                return $this->handleRefreshToken($returnArray);
            } elseif ($this->verb == "bestseller") {
                if(!isset($_GET['start'])) {
                    return "Missing parameter: start";
                }
                if(!isset($_GET['end'])) {
                    return "Missing parameter: end";
                }
                if(!isset($_GET['store'])) {
                    return "Missing parameter: store";
                }

                $start = $_GET['start'] . " 00:00:00";
                $end = $_GET['end'] . " 23:59:59";
                $storeId = $_GET['store'];
                if(!$this->isUserAuthorizedForStore($storeId)) {
                    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
                }

                $orders = $this->db->rawQuery("SELECT oi.partnumber, oi.description, sum(oi.retail) as totalprice, sum(oi.quantity) as total_quantity_sold, round((sum(oi.retail)/sum(oi.quantity)),2) as unitprice FROM `order` o LEFT JOIN orderitem oi ON o.id = oi.order_id WHERE o.type = 'I' and oi.itemtype_id in (1, 2, 3, 4) and o.updated >= ? and o.updated <= ? and o.store_id = ? group by oi.partnumber, oi.description order by total_quantity_sold desc", array($start, $end, $storeId));
                return $orders;
            } elseif($this->verb == "deletedorders") {
                if(!isset($_GET['start'])) {
                    return "Missing parameter: start";
                }
                if(!isset($_GET['end'])) {
                    return "Missing parameter: end";
                }
                if(!isset($_GET['store'])) {
                    return "Missing parameter: store";
                }

                $start = $_GET['start'] . " 00:00:00";
                $end = $_GET['end'] . " 23:59:59";
                $storeId = $_GET['store'];
                if(!$this->isUserAuthorizedForStore($storeId)) {
                    return Array("data" => Array("error" => "User does not have authoriztion to store"), "statuscode" => 401);
                }
                $contactColumn = "CASE WHEN o.extrnid is null THEN co.id ELSE co.extrnid END";
                $orders = $this->db->rawQuery("select DATE_FORMAT(o.updated, '%m/%d/%Y') as date, COALESCE(o.extrnid,concat('C',o.id)) as id, o.ordertotal, co.firstname, co.lastname from `order` o left join contact co on (o.contact_id = $contactColumn and o.store_id = co.store_id) where o.type='D' and o.updated >= ? and o.updated <= ? and o.store_id = ? order by o.id asc", array($start,$end,$storeId));
                
                $returnArray['data'] = $orders;
                return $this->handleRefreshToken($returnArray);
            }
		}
	}

    protected function accountsreceivable() {
        if($this->user == null) {
            return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
        }
        if($this->method == 'PUT'){
            $body = json_decode($this->file);
            if($this->verb == "paid") {
                foreach($body as $o) {
                    $this->db->where('id', $o->id);
                    $update = array(
                        "paydate" => date('Y-m-d G:i:s'),
                        "closedmethod" => $o->payid,
                        "is_updated" => true
                    );
                    if(isset($o->checknumber)) {
                        $update['checknumber'] = $o->checknumber;
                    }
                    $this->db->update('orderpayinfo', $update);
                }
            }
        }
    }

    protected function accountspayable() {
        if($this->user == null) {
            return Array("data" => Array("error" => "User session timed out"), "statuscode" => 401);
        }
        if($this->method == 'PUT'){
            if(!isset($_GET['store'])) {
                return $this->handleRefreshToken(array("data" => "Missing parameter: store", "statuscode" => 400));
            }

            $body = json_decode($this->file);
            if($this->verb == "paid") {
                foreach($body as $i) {
                    $this->db->where('COALESCE(extrnid, id)', $i);
                    $this->db->where('store_id', $_GET['store']);
                    $update = array("paid" => 1, "is_updated" => true);
                    $this->db->update('invoice', $update);
                }
            }
        }
    }
	
	protected function handleArray($arr) {
		if($arr == null) {return array();}
		$retArray = array();
		foreach($arr as $a) {
			$retArray[] = json_decode($a->__toString());
		}
		return $retArray;
	}
	
	protected function handleRefreshToken($arr) {
		if($arr == null) {$arr = array();}
		if($this->refreshedToken != null) {
			$arr['refreshToken'] = $this->refreshedToken;
		}
		return $arr;
	}
	
	protected function isUserAuthorizedForStore($storeId) {
	    $storeCount = $this->db->rawQueryValue('SELECT count(*) FROM `user_store` where user_id=? and store_id=? limit 1', array($this->user->userId,$storeId));
	    if($storeCount < 1) {
	        return false;
	    } else {
	        return true;
	    }
	}

    protected function pullData() {
        if($this->method == 'POST') {
        } elseif($this->method == 'GET'){
            $table = $_GET['table'];

            // pull new records added to central server
            if ($_GET['type'] && $_GET['type'] == 'sync_new_data') {
                $rows = $this->db->rawQuery(
                    "select * from `$table` where extrnid is null and store_id = ?",
                    array($_GET['store_id'])
                );

                $retData = [];
                foreach ($rows as $key => $row) {
                    $id = $row['id'];
                    unset($row['id']);
                    unset($row['extrnid']);
                    unset($row['is_updated']);

                    $retData[$key] = [
                        'table' => $table,
                        'id' => $id,
                        'data' => $row
                    ];

                    // getting teammember role_id
                    if($table == 'employee') {
                        $this->db->where('employee_id', $id)->where('store_id', $_GET['store_id']);
                        $retData[$key]['role_id'] = $this->db->getValue('teammember', 'role_id');
                    }

                    // getting teammember_id for orders
                    if($table == 'order') {
                        $this->db->where('order_id', $id)->where('store_id', $_GET['store_id']);
                        $retData[$key]['data']['teammember_id'] = $this->db->getValue('order_teammember', 'teammember_id', null);

                        // Check if contact_id and vehicle_id is central or external
                        $this->db->where('COALESCE(extrnid,id)', $row['contact_id'])->where('store_id', $_GET['store_id']);
                        $retData[$key]['contact_extrnid'] = $this->db->getValue('contact', 'extrnid');

                        // for vehicle_id
                        if ($row['vehicle_id']) {
                            $this->db->where('COALESCE(extrnid,id)', $row['vehicle_id'])->where('store_id', $_GET['store_id']);
                            $retData[$key]['vehicle_extrnid'] = $this->db->getValue('vehicle', 'extrnid');
                        }
                    }
                    // Check if order_id and vendor_id is central or external
                    if ($table == 'orderitem') {
                        $this->db->where('COALESCE(extrnid,id)', $row['order_id'])->where('store_id', $_GET['store_id']);
                        $retData[$key]['order_extrnid'] = $this->db->getValue('order', 'extrnid');

                        // for vendor_id
                        if ($row['vendor_id']) {
                            $this->db->where('COALESCE(extrnid,id)', $row['vendor_id'])->where('store_id', $_GET['store_id']);
                            $retData[$key]['vendor_extrnid'] = $this->db->getValue('vendor', 'extrnid');
                        }
                    }

                    // Check if order_id for appointment is central or external
                    if ($table == 'appointment') {
                        $this->db->where('COALESCE(extrnid,id)', $row['order_id'])->where('store_id', $_GET['store_id']);
                        $retData[$key]['order_extrnid'] = $this->db->getValue('order', 'extrnid');
                    }

                    // Check if order_id for orderpayinfo is central or external
                    if ($table == 'orderpayinfo') {
                        $this->db->where('COALESCE(extrnid,id)', $row['order_id'])->where('store_id', $_GET['store_id']);
                        $retData[$key]['order_extrnid'] = $this->db->getValue('order', 'extrnid');
                    }
                }

                return $retData;
            }

            // pull updated records from central
            if ($_GET['type'] && $_GET['type'] == 'sync_updates') {
                if ($table == 'store') {
                    $rows = $this->db->rawQuery(
                        "select * from `$table` where id = ? and is_updated",
                        array($_GET['store_id'])
                    );
                } elseif ($table == 'teammember') {
                    $rows = $this->db->rawQuery(
                        "select * from `$table` where store_id = ? and is_updated",
                        array($_GET['store_id'])
                    );
                } else {
                    $rows = $this->db->rawQuery(
                        "select * from `$table` where extrnid is not null and store_id = ? and is_updated",
                        array($_GET['store_id'])
                    );
                }

                $retData = [];
                foreach ($rows as $key => $row) {
                    if($table == 'store') {
                        unset($row['organization_id']);
                        $id = $row['id'];
                    } elseif($table == 'teammember') {
                        $id = $row['employee_id'];
                    } else {
                        $id = $row['extrnid'];
                    }
                    unset($row['id']);
                    unset($row['extrnid']);
                    unset($row['is_updated']);

                    $retData[$key] = [
                        'table' => $table,
                        'id' => $id,
                        'data' => $row
                    ];

                    // getting teammember_id for orders
                    if($table == 'order') {
                        $this->db->where('order_id', $id)->where('store_id', $_GET['store_id']);
                        $retData[$key]['data']['teammember_id'] = $this->db->getValue('order_teammember', 'teammember_id', null);
                    }
                }

                return $retData;
            }

            // pull deleted records from central
            if ($_GET['type'] && $_GET['type'] == 'deleted_data') {
                $rows = $this->db->rawQuery("select * from `$table` where store_id = ?", array($_GET['store_id']));
                return $rows;
            }
        } elseif($this->method == 'PUT'){
        } elseif($this->method == 'DELETE'){
        }    
    }

    protected function unsetPrimaryContacts($customer_id, $store_id, $contact_id = null) {
        $this->db->objectBuilder();
        $primaryContacts = $this->db->rawQuery('select * from contact where customer_id = ? and isprimary = ? and store_id = ?', array($customer_id, "true", $store_id));
        foreach ($primaryContacts as $primaryContact) {
            if($primaryContact->id != $contact_id) {
                $primaryContact->isprimary = "false";
                $primaryContact->is_updated = true;
                $this->db->where('id', $primaryContact->id);
                $this->db->update('contact', get_object_vars($primaryContact));
            }
        }
    }

    protected function setCustomerCache($id, $store_id, $cached_version) {
        $customer = new stdClass();
        $customer->cached_version = $cached_version;
        $this->db->where('COALESCE(extrnid, id)', $id);
        $this->db->where('store_id', $store_id);
        $this->db->update('customer', get_object_vars($customer));
    }

    protected function setStoreCache($id, $cached_version) {
        $store = new stdClass();
        $store->cached_version = $cached_version;
        $this->db->where('id', $id);
        $this->db->update('store', get_object_vars($store));
    }
 }