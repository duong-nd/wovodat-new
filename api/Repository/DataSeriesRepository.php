<?php
	/**
	*	This class supports query the data series (deformation, gas, seismic..) for a volcano
	* 	
	*/
	class DataSeriesRepository {

		/**
		*	Given a volcano Id, return all stations data belonged to it
		*	@param: 
		*		$vd_id
		*	@return"
		*		data list
		*/
		public static function getDataList($vd_id) {
	        $list=array("seismic"); //,"deformation","gas","field","hydrologic","thermal","meteo");
			$result = array();
        	foreach ($list as $k => $type) {
				$result = array_merge($result,self::getStation($vd_id, $type));
        	}

			return $result;
		}

		public static function getStationSeismicInterval($station,$component){
			global $db;
			$code = $station['code'];
			$result = array();
			$cc = ", b.cc_id, b.cc_id2, b.cc_id3, b.cb_ids ";
			$ts1='sd_ivl_stime';	$ts2='sd_ivl_stime';
			$te1='sd_ivl_etime';	$te2='sd_ivl_etime';
			if($component=='sd_ivl_nfelt') {
				$ts1='sd_ivl_felt_stime';	$te1='sd_ivl_felt_etime';
			}
			if($component=='sd_ivl_etot') {
				$ts1='sd_ivl_etot_stime';	$te1='sd_ivl_etot_etime';
			}
			$query = "select IFNULL(b.$ts1,b.$ts2) as stime, b.$component as component_data $cc, IFNULL(b.sd_ivl_eqtype,'Undefined') as eqtype, IFNULL(b.$te1,b.$te2) as etime from ss a, sd_ivl b where b.$component is not null and a.ss_code = '$code' and ((b.ss_id is not null and b.ss_id=a.ss_id) or (b.ss_id is null and b.sn_id is not null and b.sn_id=a.sn_id)) and a.ss_pubdate <= now() and b.sd_ivl_pubdate <= now() order by sd_ivl_etime ";
			$db->query($query);
			$value = $db->getList();
			foreach ($value as &$a) {
				$a['stime'] = TimeFormatter::getJavascriptTimestamp($a['stime']);
				$a['etime'] = TimeFormatter::getJavascriptTimestamp($a['etime']);
			}
			$result['station'] = $station;
			$result['component'] = $component;
			$result['data_series'] = $value;
			//print_r($result);
			return $result;
		}

		public static function getStationWithData($station) {
			$result = array();
			switch ($station['type']) {
				case 'seismic':
					switch($station['table']) {
						case 'interval':
							$components = array('sd_ivl_hdist','sd_ivl_avgdepth','sd_ivl_vdispers','sd_ivl_hmigr_hyp','sd_ivl_vmigr_hyp','sd_ivl_nrec','sd_ivl_nfelt','sd_ivl_etot','sd_ivl_fmin','sd_ivl_fmax','sd_ivl_amin','sd_ivl_amax');
							foreach ($components as $component) {
								$temp = self::getStationSeismicInterval($station, $component);
								if (!empty($temp['data_series'])) {
									$result[] = $temp;
								}
							}
						case 'RSAM': 
							$value = mysql_query("select c.sd_rsm_id from ss a, sd_sam b, sd_rsm c where a.ss_code = '$station[code]' and a.ss_id = b.ss_id and b.sd_sam_id = c.sd_sam_id limit 0 , 1");
							if (!empty($temp['data_series'])) {
								$result[] = $temp;
							}

						break;
					}
				break;
			}
			return $result;
		}
		/**
		*	Get available stations of a specfic type for a specific volcano
		*	@param: 
		*		$vd_id, $type
		*	@return"
		*		data list
		*
		*		(select  c.ss_code,c.ss_lat,c.ss_lon FROM sn a, ss c  where a.vd_id = 1056  and a.sn_id = c.sn_id) UNION (select c.ss_code,c.ss_lat,c.ss_lon FROM jj_volnet a, ss c , vd_inf d  WHERE a.vd_id = 1056 and a.vd_id=d.vd_id  and a.jj_net_flag = 'S' and a.jj_net_id = c.sn_id and (sqrt(power(d.vd_inf_slat - c.ss_lat, 2) + power(d.vd_inf_slon - c.ss_lon, 2))*100)<20)
		*
		*		select a.ss_id from sd_ivl a, ss b where b.ss_code = 'StHelens1201-05-SeisSta' and ((a.sn_id is not null and b.sn_id=a.sn_id) or (a.ss_id is not null and b.ss_id=a.ss_id)) limit 0,1
		*/
		public static function getStation($vd_id, $type) {
			global $db;
			$result = array();
			switch ($type) {
				case 'seismic':
					$query = "(select  c.ss_code,c.ss_lat,c.ss_lon FROM sn a, ss c  where a.vd_id = $vd_id  and a.sn_id = c.sn_id) UNION (select c.ss_code,c.ss_lat,c.ss_lon FROM jj_volnet a, ss c , vd_inf d  WHERE a.vd_id = $vd_id and a.vd_id=d.vd_id  and a.jj_net_flag = 'S' and a.jj_net_id = c.sn_id and (sqrt(power(d.vd_inf_slat - c.ss_lat, 2) + power(d.vd_inf_slon - c.ss_lon, 2))*100)<20)";
					$db->query($query);
					$stations = $db->getList();
					foreach ($stations as $x => $temp) {
						// get the station code
						$code = $temp['ss_code'];
						// sd_ivl
						$query = "select a.ss_id from sd_ivl a, ss b where b.ss_code = '$code' and ((a.sn_id is not null and b.sn_id=a.sn_id) or (a.ss_id is not null and b.ss_id=a.ss_id)) limit 0,1";
						$db->query($query);
						$value = $db->getList();
						if($value && !empty($value)) {
							$array = array(
    							"type" => "seismic",
    							"table" => "interval",
    							"code"	=> $code,
    							"lat"	=> $temp['ss_lat'],
    							"lon"	=> $temp['ss_lon'],
							);
							$result = array_merge($result, self::getStationWithData($array));
						}
					}
				break;
           	}
			return $result;
       }
   	}
	/* sd_rsm
	                    $value = mysql_query("select c.sd_rsm_id from ss a, sd_sam b, sd_rsm c where a.ss_code = '$code' and a.ss_id = b.ss_id and b.sd_sam_id = c.sd_sam_id limit 0 , 1");
	                    if ($value && mysql_num_rows($value)) {
	                        $this->chkNull("Seismic&RSAM&$code&$temp[1]&$temp[2];");
	                        break;
	                    }

	// sd_ssm
	                    $value = mysql_query("select c.sd_ssm_id from ss a, sd_sam b, sd_ssm c where a.ss_code = '$code' and a.ss_id = b.ss_id and b.sd_sam_id = c.sd_sam_id limit 0 , 1");
	                    if ($value && mysql_num_rows($value)) {
	                        $this->chkNull("Seismic&SSAM&$code&$temp[1]&$temp[2]&lowf&Hz;");
	                        $this->chkNull("Seismic&SSAM&$code&$temp[1]&$temp[2]&highf&Hz;");
	                        $this->chkNull("Seismic&SSAM&$code&$temp[1]&$temp[2]&count;");
	                        break;
	                    }


	// sd_evs
	                    $value = mysql_query("select b.ss_id from ss a, sd_evs b where a.ss_code = '$code' and a.ss_id = b.ss_id  limit 0 , 1");
	                    if ($value && mysql_num_rows($value)) {
	                        $this->chkNull("Seismic&EVS&$code&$temp[1]&$temp[2]&SPInterval;");
	                        $this->chkNull("Seismic&EVS&$code&$temp[1]&$temp[2]&MaxAmpl;");
	                        $this->chkNull("Seismic&EVS&$code&$temp[1]&$temp[2]&Duration;");
	                    }

	// sd_int
	                    $value = mysql_query("select c.ss_id from ss a, sd_evs b, sd_int c where a.ss_code = '$code' and a.ss_id = b.ss_id and b.sd_evs_id=c.sd_evs_id  limit 0 , 1");
	                    if ($value && mysql_num_rows($value)) {
	                        $this->chkNull("Seismic&INT&$code&$temp[1]&$temp[2]&MaxDist&km;");
	                        $this->chkNull("Seismic&INT&$code&$temp[1]&$temp[2]&MaxRInt&km;");
	                        $this->chkNull("Seismic&INT&$code&$temp[1]&$temp[2]&MaxRIntDist&km;");
	                    }           

	                    $value = mysql_query("select b.sd_trm_id from ss a, sd_trm b where a.ss_code = '$code' and ((b.sn_id is not null and b.sn_id=a.sn_id) or (b.ss_id is not null and b.ss_id=a.ss_id)) limit 0,1")                ;
	                    if ($value && mysql_num_rows($value)) {
	                        $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DurDay&min;");
	                        $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DomFreq1&Hz;");
	                        $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DomFreq2&Hz;");
	                        $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&MaxAmp;");
	                        $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&RedDis;");                        
	                    }
	                }

	// sd_trm
	                    
	                $value = mysql_query("select b.sd_trm_id from ss a, sd_trm b where a.ss_code = '$code' and ((b.sn_id is not null and b.sn_id=a.sn_id) or (b.ss_id is not null and b.ss_id=a.ss_id)) limit 0,1")                ;
	                if ($value && mysql_num_rows($value)) {
	                    $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DurDay&min;");
	                    $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DomFreq1&Hz;");
	                    $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&DomFreq2&Hz;");
	                    $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&MaxAmp;");
	                    $this->chkNull("Seismic&TRM&$code&$temp[1]&$temp[2]&RedDis;");                        
	                }
	// sd_ivl by network
	                $networks = mysql_query("select distinct(a.sn_code) from sn a, sd_ivl b where b.ss_id is null and b.sn_id = a.sn_id");
	                while($code = mysql_fetch_array(($networks))) {
	                    $this->chkNull("Seismic&Interval&$code&0&$0&hdist&km;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&avgdepth&m;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&vdispers&km;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&hmigr_hyp&km;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&vmigr_hyp&km;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&nrec;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&nfelt;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&etot&erg;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&fmin&Hz;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&fmax&Hz;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&amin;");
	                    $this->chkNull("Seismic&Interval&$code&0&$0&amax;");
	                }

*/
