<?php
include_once(APPPATH.'models/base_model.php');
class Tweet_model extends Base_model
{
	
	public function __construct() 
	{
		parent::__construct();
	}

	
	public function get() {
		$sql = sprintf('SELECT * FROM '.$this->db->USER_TWEETS.' order by id desc');
		$query = $this->db->query($sql);
		$result_arr = $query->result_array();

		return $result_arr;
	}
	
	
	public function get_by_id($id, $start_limit="", $no_of_page="") {
		if("$start_limit" == "") {
			$sql = sprintf('SELECT * FROM '.$this->db->USER_TWEETS.'  where id = %s',  $id);
		}
		else {
			$sql = sprintf('SELECT * FROM '.$this->db->USER_TWEETS.'  where id = %s limit %s, %s',  $id, $start_limit, $no_of_page);
		}

		$query = $this->db->query($sql);
		$result_arr = $query->result_array();
		
		return $result_arr[0];
	}
	


 ### GET ALL THE TWEETS OF NETPALS AND FRIENDS
	
	public function get_friends_netpals_tweets_by_user_id($i_user_id, $s_where, $i_start_limit='', $i_no_of_page='') {
		
		if("$i_start_limit" == "") {
			$sql = sprintf("
				  (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					
					AND
					(
						t.i_user_id in 
						(SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
							and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
							or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %5\$s
					
					
					
					) )

				UNION 

				 (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						 
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id))) %5\$s
					) )

				ORDER BY dt_created_on DESC
					"
				, $this->db->dbprefix, intval($i_user_id), $s_where
			);
		}
		else {
		
		
		
			 $sql = sprintf("
				(SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					
					AND
					(
						t.i_user_id in 
						(SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
							and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
							or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %5\$s
					
					
					
					) )

				UNION 

				 (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						 
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id)))  %5\$s
					) )
					
				    ORDER BY dt_created_on DESC
					limit %3\$s, %4\$s
					"
				, $this->db->dbprefix, intval($i_user_id), intval($i_start_limit), intval($i_no_of_page),  $s_where
			);
		}

#AND t.i_user_id != '%2\$s'
		$query = $this->db->query($sql); ///echo "sql ==>". nl2br($sql) ."<br />"; 
		$result_arr = $query->result_array();
        
     // pr($result_arr);
     
		return $result_arr;
		
	
	}
	
	
	

	public function get_total_friends_netpals_tweets_by_user_id($i_user_id,  $s_where) {
		

		 $sql = sprintf("
				SELECT COUNT(*) count FROM (
				(SELECT t.id
						
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					(
						t.i_user_id in (SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
						and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
						or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %3\$s
					
					
					
					) )

				UNION 
				
				(SELECT t.id
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					AND
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id)))  %3\$s
					) )

				
				) derived_tbl
					"
				, $this->db->dbprefix, intval($i_user_id),$s_where
			);
		
#and t.i_user_id != '%2\$s'
		$query = $this->db->query($sql); //echo "sql ==>". nl2br($sql) ."<br />";  
		$result_arr = $query->result_array();

		return $result_arr[0]['count'];
	
	}
### GET ALL THE TWEETS OF NETPALS AND FRIENDS	
	

	
	
### GET ALL TWEETS BY LOGGED USER ID ##########33
public function get_all_tweets_by_user_id($i_user_id, $s_where, $i_start_limit='', $i_no_of_page='') {
		
		if("$i_start_limit" == "") {
			$sql = sprintf("
				  (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					
					AND
					(
						t.i_user_id in 
						(SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
							and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
							or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %5\$s
					
					
					
					) )

				UNION 

				 (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						 
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id))) %5\$s
					) )
					
					UNION
				(SELECT u.id i_user_id,
					u.s_email,
					u.e_gender,
					CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
					u.s_profile_photo,
					
					t.id,
					t.i_user_id,
					t.s_tweet_text,
					t.dt_created_on
					
					FROM cg_users u, cg_user_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND t.i_user_id = %2\$s)

				ORDER BY t.id DESC
					"
				, $this->db->dbprefix, intval($i_user_id), $s_where
			);
		}
		else {
		
		
		
			 $sql = sprintf("
				(SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					
					AND
					(
						t.i_user_id in 
						(SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
							and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
							or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %5\$s
					
					
					
					) )

				UNION 

				 (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						 
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id)))  %5\$s
					) )
					
				UNION
				(SELECT u.id i_user_id,
					u.s_email,
					u.e_gender,
					CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
					u.s_profile_photo,
					
					t.id,
					t.i_user_id,
					t.s_tweet_text,
					t.dt_created_on
					
					FROM cg_users u, cg_user_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND t.i_user_id = %2\$s  %5\$s)

				    ORDER BY dt_created_on DESC
					limit %3\$s, %4\$s
					"
				, $this->db->dbprefix, intval($i_user_id), intval($i_start_limit), intval($i_no_of_page),  $s_where
			);
		}

#AND t.i_user_id != '%2\$s'
		$query = $this->db->query($sql);
		$result_arr = $query->result_array();
        
     #pr($result_arr);
     
		return $result_arr;
		
	
	}
	
	public function get_total_all_tweets_by_user_id($i_user_id,  $s_where) {
		

		 $sql = sprintf("
				SELECT COUNT(*) count FROM (
				(SELECT t.id
						
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id AND
					(
						t.i_user_id in (SELECT u.id from %1\$suser_contacts c, %1\$susers u where c.s_status = 'accepted'
						and ((c.i_requester_id = %2\$s and u.id=c.i_accepter_id) 
						or (c.i_accepter_id = %2\$s and u.id=c.i_requester_id)) 
						)  %3\$s
					
					
					
					) )

				UNION 
				
				(SELECT t.id
					FROM %1\$susers u, %1\$suser_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1  AND t.i_user_id = u.id 
					AND
					(
						t.i_user_id in  (SELECT u.id from %1\$susers_net_pal_contacts n, %1\$susers u where n.s_status = 'accepted'
						AND ((n.i_requester_id = %2\$s AND u.id=n.i_accepter_id) 
						OR (n.i_accepter_id = %2\$s AND u.id= n.i_requester_id)))  %3\$s
					) )
				UNION
				(SELECT t.id 
					FROM cg_users u, cg_user_tweets t
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1 
					 AND t.i_user_id = u.id AND t.i_user_id = %2\$s  %3\$s)

				
				) derived_tbl
					"
				, $this->db->dbprefix, intval($i_user_id),$s_where
			);
		
#and t.i_user_id != '%2\$s'
		$query = $this->db->query($sql); //echo "sql ==>". ($sql) ."<br />";  
		$result_arr = $query->result_array();
		#echo $result_arr[0]['count']; exit;
		return $result_arr[0]['count'];
	
	}
	
	
### GET ALL TWEETS BY LOGGED USER ID ##########



### GET ALL TWEETS of only LOGGED USER ID ##########33
public function get_my_tweets($i_user_id, $s_where, $i_start_limit='', $i_no_of_page='') {
		
		if("$i_start_limit" == "") {
			$sql = sprintf("
				  (SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1 
					 AND t.i_user_id = u.id AND t.i_user_id = %2\$s %3\$s
					ORDER BY dt_created_on DESC
					"
				, $this->db->dbprefix, intval($i_user_id), $s_where
			);
		}
		else {
		
		
		
			 $sql = sprintf("
				(SELECT  u.id i_user_id, 
						 u.s_email, 
						 u.e_gender, 
						 CONCAT(u.s_first_name,' ', u.s_last_name) s_profile_name,
						 u.s_profile_photo, 
						
						 t.id,
						 t.i_user_id,
						 t.s_tweet_text,
						 t.dt_created_on
						
					FROM %1\$susers u, %1\$suser_tweets t
					
					WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1 
					AND t.i_user_id = u.id AND t.i_user_id = %2\$s %5\$s
				)

				    ORDER BY dt_created_on DESC
					limit %3\$s, %4\$s
					"
				, $this->db->dbprefix, intval($i_user_id), intval($i_start_limit), intval($i_no_of_page),  $s_where
			);
		}

#AND t.i_user_id != '%2\$s'
		$query = $this->db->query($sql); //echo "sql ==>". ($sql) ."<br />"; 
		$result_arr = $query->result_array();
        
     // pr($result_arr);
     
		return $result_arr;
		
	
	}
	
	public function get_total_my_tweets($i_user_id,  $s_where) {
		

		 $sql = sprintf("
				SELECT COUNT(*) count FROM (
					(SELECT t.id 
					  FROM cg_users u, cg_user_tweets t
					  WHERE u.i_status='1' AND u.i_isdeleted ='1' AND t.i_isenabled =1 
					  AND t.i_user_id = u.id AND t.i_user_id = %2\$s %3\$s)

				
				) derived_tbl
					"
				, $this->db->dbprefix, intval($i_user_id),$s_where
			);
		
#and t.i_user_id != '%2\$s'
		$query = $this->db->query($sql); //echo "sql ==>". nl2br($sql) ."<br />";  
		$result_arr = $query->result_array();

		return $result_arr[0]['count'];
	
	}
	
	
### GET ALL TWEETS of only LOGGED USER ID ##########33
	
	
	

	public function insert($arr=array()) {
		if(count($arr)==0) {
			return null;
		}
		$this->db->insert($this->db->USER_TWEETS, $arr);# echo $this->db->last_query();
		return $this->db->insert_id();
	}
	
	

	public function update($arr=array(), $id) {
		if(count($arr)==0) {
			return null;
		}
		$this->db->update($this->db->USER_TWEETS, $arr, array('id'=>$id));
	}
	
	

	public function delete_by_id($id) {
	
	     $sql = sprintf( 'DELETE FROM '.$this->db->USER_TWEETS.' WHERE id=%s', $id );
		 $this->db->query($sql);
		#echo $this->db->last_query(); exit;
	}
	
	public function get_list($where='',$i_start=null,$i_limit=null,$s_order_by='')
    {
        
        $limit  = (is_numeric($i_start) && is_numeric($i_limit))?" Limit ".intval($i_start).",".intval($i_limit):'';
		$s_order_by = ($s_order_by != '')?'ORDER BY '.$s_order_by :'ORDER BY id DESC';
		
        $sql    = " SELECT T.*, 
					CONCAT(U.s_first_name,' ', U.s_last_name) AS s_profile_name
					FROM {$this->db->USER_TWEETS} 
					T LEFT JOIN  {$this->db->USERS} U ON U.id = T.i_user_id 
					{$where}  {$s_order_by} {$limit}";

        $query     = $this->db->query($sql); //echo $this->db->last_query();
        $result_arr = $query->result_array(); //pr($result_arr,1);
		
		
        return $result_arr;
    }
	
    public function get_list_count($where='')
    {
        
        
        $sql    = "SELECT count(*) as i_total FROM {$this->db->USER_TWEETS} T 
				  	LEFT JOIN  {$this->db->USERS} U ON U.id = T.i_user_id  
				  	{$where} ";
        $query     = $this->db->query($sql); //echo $this->db->last_query();
        $result_arr = $query->result_array(); //pr($result_arr);
        return $result_arr[0]['i_total'];
    }
	
	public function change_status($status ,$id) {
		
	  if($status !='' && $id !=''){	
		  $sql = sprintf( "UPDATE {$this->db->USER_TWEETS} SET `i_isenabled` = '%s'
						   WHERE `id` ='%s'"
					  , $status, $id );
		  $this->db->query($sql);// echo $this->db->last_query();exit;
		  return true;
	  }
	}
	
	
	public function get_owner_by_tweet_id($id) {
		
		$sql = sprintf('SELECT i_user_id as i_owner_id  FROM '.$this->db->USER_TWEETS.'  WHERE id = %s ',$id);
		$query = $this->db->query($sql);
		$result_arr = $query->result_array();
		return $result_arr[0];
	}
	
}
