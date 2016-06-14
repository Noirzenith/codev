<?php
   require('../path.inc.php');
   
	class Rest_Api extends Rest {
      
      private static $logger;
      
      private $sessionUser;
      private $currentTeam;
      private $data;
      
      public static function staticInit() {
         self::$logger = Logger::getLogger(__CLASS__);
      }
      
		public function __construct(){
			parent::__construct();				// Init parent contructor
		}
		
		/**
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['rquest']))); 		
			if((int)method_exists($this,$func) > 0)
         {
            switch ($func) {
               case "connexion":
               case "authentification":
                   $this->$func();;
                   break;
               case "extractdata":
                   $this->response('',401);	
                   break;
               default:
                  $this->extractData();
                  if($this->authentification()){
                     $this->$func();
                  }
                  else{
                     $this->response('',204);	
                  }
               break;
            }
         }
         else{
            $this->response('',204);	
         }
//            if($func == "connexion" || $func == "authentification" ){
//               $this->$func();
//            }
//				else{
//               if($this->authentification()){
//                  $this->$func();
//               }
//               else{
//                  $this->response('',204);	
//               }
//            }
//         }
//			else{
//            $this->response('',204);	
//         }
			// If the method not exist with in this class, response would be "Page not found".
		}
		
      
		/**
		 * Simple verif exist preco
		 * numanc : <NUMANC>
		 */
		private function preco(){
			if($this->get_request_method() != "GET"){ $this->response('',406); } 
			$my_preco=new Model_Precos;
			$numanc=$this->_request['numanc'];
			$res=$my_preco->fetchRow("numanc='$numanc'");
			if(is_object($res)){
				$this->response($this->json($res->toArray()), 200);
			}else{
				$this->response('',204);
			}
		}
		
		/**
		* Suppression preco
		* numanc : $numanc
		*/
		private function suppr_res(){
			if($this->get_request_method() != "DELETE"){ $this->response('',406); }
		   $my_preco=new Model_Precos;
		   $numanc=$this->_request['numanc'];
		   $ret=$my_preco->delete($numanc);
		   If($ret){
			   $success = array('status' => "Success", "msg" => "Element supprim�.");
			   $this->response($this->json($success),200);
		   }else{
			   $this->response('',204);
		   }
		}
		
		/**
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
      
      private function connexion() {
         $res = openssl_pkey_get_private(file_get_contents('C:\xampp\apache\bin\sigsso_private.key'), 'codevtt');
         $pubkeyArray=openssl_pkey_get_details($res);
         $pubkey=$pubkeyArray["key"];
         $this->response($pubkey,200);
      }
      
      private function extractData() {
//         $this->data = $this->_request['data'];
         $encrypted = $this->_request['data'];        
         $res = openssl_pkey_get_private(file_get_contents('C:\xampp\apache\bin\sigsso_private.key'), 'codevtt');
         openssl_private_decrypt($encrypted,$data,$res);
         $this->data = $data;
//         self::$logger->error($encryptedData);
//         parse_str($data, $this->data);
      }
      
      private function authentification() {
         $token = $this->data;
         $sql = "SELECT id FROM `codev_users_table` WHERE resttoken = '$token'";
         $result = SqlWrapper::getInstance()->sql_query($sql);
         self::$logger->error($token);
         if(SqlWrapper::getInstance()->sql_num_rows($result) == 0) {
            return false;
         }
         $row = SqlWrapper::getInstance()->sql_fetch_object($result);
         self::$logger->error($row);
         $this->sessionUser = UserCache::getInstance()->getUser($row->id);
         return true;
      }
      
      /**
       * @rest_param1 userid 
       */
      private function getName() {
         $this->response($this->sessionUser->getName(),200);
      }
      
      
      
      
	}
	
      
   Rest_Api::staticInit();
   
	// Initiiate Library
	$api = new Rest_Api;
	$api->processApi();

