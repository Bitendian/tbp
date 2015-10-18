<?php

require_once(TBP_BASE_PATH . '/interfaces/i_render.php');

abstract class abstract_renderizable implements i_render {

	private $_rendered_html = null;

	function run_render() {
		return $this->__toString();
	}

	public function __toString() {
		if ($this->_rendered_html === null) {
			try {
				if (ob_start()) {
					$this->render();
					$this->_rendered_html = ob_get_contents();
					ob_end_clean();
				}
			} catch(Exception $e) {
				// TODO add log for exception
			}
		}
		return $this->_rendered_html;
	}
}
