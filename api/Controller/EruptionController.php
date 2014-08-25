<?php
	class EruptionController {
		/**
		*	Return eruption list belonging to a specific volcano
		*	@param: 
		*		volcano_id
		*	@return:
		*		eruption list
		*/
		public static function loadEruptionForecastList($vd_id) {
			return EruptionRepository::getEruptionForecastList($vd_id);
		}

		/**
		*	Return eruption list belonging to a specific volcano
		*	@param: 
		*		volcano_id
		*	@return:
		*		eruption list
		*/
		public static function loadEruptionList($vd_id) {
			return EruptionRepository::getEruptionList($vd_id);
		}
	}