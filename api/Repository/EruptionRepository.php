<?php
	/**
	*	This class supports query the information from ed and ed_phs table
	* 	
	*/
	class EruptionRepository {

		/**
		*	Given a volcano Id, return all eruption belonged to it
		*	@param: 
		*		$vd_id
		*	@return"
		*		eruption list
		*/
		public static function getEruptionList($vd_id) {
			$result = array();
			$ed_idList = self::getEruptionIdList($vd_id);
			foreach ($ed_idList as $ed_idRow) {
				array_push($result, self::getEruption($ed_idRow["ed_id"]));
			}
			return $result;
		}

		/**
		*	Given a volcano Id, return list of Eruption Id belonged to it
		*	@param:
		*		$vd_id
		*	@return:
		*		list of eruption id
		*/
		public static function getEruptionIdList($vd_id) {
			global $db;
			$query = "select ed_id from ed where ed.vd_id = %d";
			$db->query($query, $vd_id);
			return $db->getList();
		}

		/**
		*	Given an eruption id, return its information
		*	@param:
		*		$ed_id
		*	@return
		*		information of this eruption
		*/
		public static function getEruption($ed_id) {
			$result = array();
			global $db;
			$query = "select ed_id, ed_stime, ed_etime,ed_vei from ed where ed.ed_id = %d";
			$db->query($query,$ed_id);
			$result = $db->getRow();
			$result['ed_stime'] = TimeFormatter::getJavascriptTimestamp($result['ed_stime']);
			$result['ed_etime'] = TimeFormatter::getJavascriptTimestamp($result['ed_etime']);
			$result["ed_phs"] = array();
			$ed_phs_idList = self::getEruptionPhaseIdList($ed_id);
			foreach($ed_phs_idList as $ed_phs_idRow) {
				array_push($result["ed_phs"], self::getEruptionPhase($ed_phs_idRow["ed_phs_id"]));
			}
			return $result;
		}


		/**
		*	Given an eruption id, return list of eruption phase id belonged to it
		*	@param:
		*		$ed_id
		*	@return:
		*		list of eruption phase id
		*/
		public static function getEruptionPhaseIdList($ed_id) {
			global $db;
			$query = "select ed_phs_id from ed_phs where ed_phs.ed_id = %d";
			$db->query($query, $ed_id);
			return $db->getList();
		}

		/**
		*	Given eruption phase id, return its information
		*	@param:
		*		$ed_phs_id
		*	@return
		*		eruption phase information
		*/
		public static function getEruptionPhase($ed_phs_id) {
			global $db;
			$query = "select ed_phs_id, ed_phs_type, ed_phs_stime, ed_phs_etime, ed_phs_vei, ed_phs_dre_tot,
					ed_phs_dre_lav, ed_phs_dre_tep, ed_phs_col from ed_phs where ed_phs.ed_phs_id = %d";
			$db->query($query, $ed_phs_id);
			$result = $db->getRow();
			$result['ed_phs_stime'] = TimeFormatter::getJavascriptTimestamp($result['ed_phs_stime']);
			$result['ed_phs_etime'] = TimeFormatter::getJavascriptTimestamp($result['ed_phs_etime']);
			return $result;
		}

		/**
		*	Given eruption forecast id, return its information
		*	@param:
		*		$ed_for_id
		*	@return:
		*		eruption forecast information
		*/
		public static function getEruptionForecast($ed_for_id) {
			global $db;
			$query = "select ed_for_id, ed_for_alevel, ed_for_astime, ed_for_aetime from ed_for where ed_for_id = %d";
			$db->query($query, $ed_for_id);
			$result = $db->getRow();
			$result['ed_for_astime'] = TimeFormatter::getJavascriptTimestamp($result['ed_for_astime']);
			$result['ed_for_aetime'] = TimeFormatter::getJavascriptTimestamp($result['ed_for_aetime']);
			return $result;
		}

		/**
		*	Return this list of eruption forecast by volcano id
		*	@param:
		*		$vd_id
		*	@return:
		*		list of eruption forecast
		*/
		public static function getEruptionForecastList($vd_id) {
			global $db;
			$query = "select ed_for_id, ed_for_alevel, ed_for_astime, ed_for_aetime from ed_for,ed_phs where vd_id = 1056 and ed_phs.ed_phs_id = ed_for.ed_phs_id ";
			$db->query($query, $vd_id);
			$result = $db->getList();
			foreach($result as &$value) {
				$value['ed_for_astime'] = TimeFormatter::getJavascriptTimestamp($value['ed_for_astime']);
				$value['ed_for_aetime'] = TimeFormatter::getJavascriptTimestamp($value['ed_for_aetime']);
			}
			return $result;
		}
	}
