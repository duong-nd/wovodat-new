<?php
	class DataSeriesController {

		/**
		*	@param: 
		*		$vd_id
		*	@return 
		*		data list
		*/
		public static function loadDataList($vd_id) {
			return DataSeriesRepository::getDataList($vd_id);
		}	

	}
