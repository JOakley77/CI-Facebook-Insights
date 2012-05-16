<?php
class Insights_Test extends CI_Controller {

	public $vars;
	
	public function __construct() {
		parent::__construct();

		$this->load->library('insights');
	}

	public function index() {
		$token = $this->insights->authorize_insights();

		if ($token) {
			$this->vars['token'] 	= $token;
			$this->vars['view'] 	= 'insights_default';
			$this->load->view('insights', $this->vars);
		}
	}

	public function results() {
		$this->vars['insights'] = $this->insights->get_results();
		$this->vars['view']		= 'insights_results';
		$this->load->view('insights', $this->vars);
	}

}