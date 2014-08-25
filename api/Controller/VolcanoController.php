<?php
	class VolcanoController {

		/**
		*	@return 
		*		volcano list
		*/
		public static function loadVolcanoList() {
			$result = VolcanoRepository::getVolcanoList();
			return $result;
		}	

	}
