<?php
/*********
* Author: 
* Date  : 
* Modified By: 
* Modified Date:
* 
* Purpose:
*  Model For  * Utility model is to define all db/functionality
 * related functions used throughout the site
* 
* @package 
* @subpackage 
* 
* @link InfModel.php 
* @link Base_model.php
* @link controllers/
* @link views/
*/

class Utility_model extends Base_model 
{

        # constructor definition...
     public function __construct() 
    {
        try
        {
          parent::__construct();
          $this->conf = get_config();
        }
        catch(Exception $err_obj)
        {
            show_error($err_obj->getMessage());
        }    
    }


        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        #               DISPLAYORDER RELATED FUNCTIONs [BEGIN]
        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 
            /* Ranking Row Create Starts */
            function RankingRowCreate($OrderValue, $record_id, $album_id= "" , $tbl="", $where="") 
            {
				//echo '<br/>'.$OrderValue.' ==  rec '. $record_id.' album '. $album_id;
                $tbl = ( !empty($tbl) )? $tbl: $this->db->USER_PHOTOS;
                
                $where = ( empty($where) )? "": $where;
                
                // get selected table's max-rank...
                $maxSQL = sprintf("SELECT IFNULL(MAX(i_order),1) AS `max_displayorder`
                          FROM %s %s", $tbl, $where);
                $query = $this->db->query($maxSQL); //echo $this->db->last_query();
                $row = $query->row(); 
         	    $MAX_DISPLAYORDER = $row->max_displayorder;
				
				
				  // get selected table's min-rank...
                $minSQL = sprintf("SELECT IFNULL(MIN(i_order),1) AS `min_displayorder`
                          FROM %s %s", $tbl, $where);
                $query_min = $this->db->query($minSQL);
                $row_min = $query_min->row(); 
         		$MIN_DISPLAYORDER = $row_min->min_displayorder;

                $ImgRank="";
				
                $if_one_record_query = sprintf("SELECT count(*) as total_row FROM %s %s",$tbl,$where);
                $if_one_record = $this->db->query($if_one_record_query)->row();
                //pr($if_one_record);
                $only_one_record = $if_one_record->total_row;
                
                if($OrderValue==2 || $only_one_record ==1)
                {
                    if($OrderValue==$MAX_DISPLAYORDER)
                      $ImgRank = '';
                    else
                        $ImgRank = "<a href='javascript:void(0)' onClick=\"displayOrderAJAX($record_id, $album_id, 'up')\"><img src='images/up.png' alt='' /></a>";
                }
                elseif($OrderValue < $MAX_DISPLAYORDER &&  $OrderValue != $MIN_DISPLAYORDER )
				{ 
                    $ImgRank="<a href='javascript:void(0)' onClick=\"displayOrderAJAX($record_id, $album_id, 'dn')\"><img src='images/dwn.png' alt='' /></a><a href='javascript:void(0)' onClick=\"displayOrderAJAX($record_id, $album_id, 'up')\"><img src='images/up.png' alt='' /></a>";
				}
				elseif($OrderValue < $MAX_DISPLAYORDER && $OrderValue == $MIN_DISPLAYORDER)
				   $ImgRank = "<a href='javascript:void(0)' onClick=\"displayOrderAJAX($record_id, $album_id, 'up')\"><img src='images/up.png' alt='' /></a>";
                elseif($OrderValue == $MAX_DISPLAYORDER)
                     $ImgRank = "<a href='javascript:void(0)' onClick=\"displayOrderAJAX($record_id, $album_id, 'dn')\"><img src='images/dwn.png' alt='' /></a>";

                return $ImgRank;

            }
            /* Ranking Row Create Ends */

        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        #               DISPLAYORDER RELATED FUNCTIONs [END]
        # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	/* Manage Ranking-Order Starts */
				function Ranking($to, $id, $where='', $tbl='')
				{
                $tbl = ( !empty($tbl) )? $tbl: $this->db->USER_PHOTOS;
             // echo $to.' -- id: '.$id ; 
                /* Ranking Up Starts */
                if($to=='dn')
                {
                    $query_pageorder = sprintf("SELECT `i_order` FROM %s WHERE `id` = %s ",
                                                $tbl, $id);
                    $query1 = $this->db->query($query_pageorder); 
                    $row1 = $query1->row();
                    $pageOrder=$row1->i_order;

                    $query_pageid=sprintf("SELECT `id` FROM  %s WHERE `i_order`=(%s-1) %s",
                                                                 $tbl, $pageOrder , $where);
                    $query2 = $this->db->query($query_pageid); 
                    $row2 = $query2->row();
             	    $pageid=$row2->id;

                    $upt_pageorder = sprintf("UPDATE %s SET `i_order`=(%s-1)
                                   WHERE `id` = %s ", $tbl, $pageOrder, $id);
                    $this->db->query($upt_pageorder); 

                    $upt_pageorder1 = sprintf("UPDATE %s SET `i_order`=%s
                                    WHERE `id` = %s ", $tbl, $pageOrder, $pageid);
                    $this->db->query($upt_pageorder1); 

                }
                /* Ranking Up Ends */

                /* Ranking Down Starts */
                if($to == 'up')
                {
                    $query_pageorder = sprintf("SELECT `i_order` FROM %s WHERE `id` = %s ",
                                                                          $tbl, $id);
                    $query1 = $this->db->query($query_pageorder);
                    $row1 = $query1->row();
                    $pageOrder=$row1->i_order;

                    $query_pageid=sprintf("SELECT `id` FROM  %s WHERE `i_order`=(%s+1)  %s ",
                                                                  $tbl, $pageOrder, $where);
                    $query2 = $this->db->query($query_pageid);
                    $row2 = $query2->row();
                    $pageid=$row2->id;

                    $upt_pageorder = sprintf("UPDATE %s SET `i_order`=(%s+1) WHERE `id` = %s ",
                                                                      $tbl, $pageOrder, $id);
                    $this->db->query($upt_pageorder);

                    $upt_pageorder1 = sprintf("UPDATE %s SET `i_order`=%s WHERE `id` = %s ",
                                                                        $tbl, $pageOrder, $pageid);
                    $this->db->query($upt_pageorder1);
                }
                /* Ranking Down Ends */

            }
			/* Manage Ranking-Order Ends */


            /* function to get maximum display-order [in time of INSERT action] */
            function getMaxDisplayOrder($tbl="", $where="")
            {
                $tbl = ( !empty($tbl) )? $tbl: $this->db->USER_PHOTOS;
                
                $where = ( empty($where) )? " WHERE `i_is_active` = 1 ": $where;
                
                $SQL = sprintf("SELECT IFNULL(MAX(`i_displayorder`)+1,1) AS `max_displayorder` FROM %s %s",
                                             $tbl, $where);
                $query = $this->db->query($SQL);
                $rows = $query->row();

                return $rows->max_displayorder;
            }
			
			
			

            /* Rearrange DisplayOrder [in case of delete/remove] Starts */
            function RearrangeOrder($pID, $tbl='', $where='')
            {
                $tbl = ( !empty($tbl) )? $tbl: $this->db->USER_PHOTOS;
                
				$WHERE_COND = ( empty($where) )? " `i_is_active` = 1 ": $where;
				
                $SQL1 = sprintf("SELECT `i_displayorder` FROM %s WHERE `id` = %s AND %s ",
                                                $tbl, $pID, $WHERE_COND);
                $query1 = $this->db->query($SQL1);

                $row1 = $query1->row();
                $DisplayOrder = $row1->i_displayorder;

                $SQL2 = sprintf("SELECT `id`, `i_displayorder`
								 FROM %s
								 WHERE `i_displayorder` > %s AND %s
								 ORDER BY `i_displayorder` ASC ", $tbl, $DisplayOrder, $WHERE_COND);
                $query2 = $this->db->query($SQL2);
                $rows = $query2->result_array();

                $recCount = count($rows);

                for($i=0; $i<$recCount; $i++)
                {
                      $prevContentId = $rows[$i]['id'];
                      $prevDisplayOrder = $rows[$i]['i_displayorder'];

                      $newDisplayOrder = $prevDisplayOrder - 1;

                      $updtSQL = sprintf("UPDATE %s
										  SET `i_displayorder` = %s
										  WHERE `id` = %s AND %s ", $tbl, $newDisplayOrder, $prevContentId, $WHERE_COND);
                      $this->db->query($updtSQL);
                }

            }

}   // end of class definition...
