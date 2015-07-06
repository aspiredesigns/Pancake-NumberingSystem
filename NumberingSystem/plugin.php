<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Numbering System
 *
 * A simple numbering system for PancakeApp. Introducing the new plugin system.
 *
 * @author		Kong Jin Jie
 * @copyright	Copyright (c) 2013, Aspire Designs Pte Ltd
 * @link		http://www.github.com
 * @since		Version 1.0
 */

class Plugin_NumberingSystem extends Plugin {

	/**
	 * Some configuration for the plugin
	 */
	public $alias = 'numbering_system';
	
	public $name = array(
		'en'	=> 'Numbering System',
	);
	
	public $description = array(
		'en'	=> 'A simple plugin to define your own numbering system',
	);
	
	public $author = 'Aspire Designs Pte Ltd';
	
	public $url = 'http://www.aspiredesigns.com.sg/';
	
	/**
	 * Configurables for the plugin and their default values
	 */
	 
	public $config = array(
		'fields' => array(
			'invoice_pattern'	=> array(
				'name'	=> 'invoice_pattern',
				'label'	=> array(
					'en'	=> 'Invoices Pattern',
				),
				'type'	=> 'text',
				'default'	=> 'INV-{yyyy}{mm}{dd}-{num4}',
			),
			'estimate_pattern' => array(
				'name' => 'estimate_pattern',
				'label' => array(
					'en' => 'Estimates Pattern'
				),
				'type' => 'text',
				'default' => 'EST-{yyyy}{mm}{dd}-{num4}'
			),
			'credit_note_pattern' => array(
				'name' => 'credit_note_pattern',
				'label' => array(
					'en' => 'Credit Notes Pattern'
				),
				'type' => 'text',
				'default' => 'CN-{yyyy}{mm}{dd}-{num4}'
			),
		),
	);
	
	
	/**
	 * Nothing interesting here...
	 */
	
	private $ci;
	
	private $installed = FALSE;
	
	
	public function __construct() {
		$this->ci =& get_instance();
		
		$this->ci->load->model('plugins_m');
		
		$this->installed = $this->get("installed");
		
		if ($this->installed) {
			// Only register events when the plugin is installed
			$this->register_events();
		}
	}
	
	private function register_events() {
		/**
		 * Register events to be hooked.
		 * Add more if required. :)
		 */
		 
		Events::register('before_invoice_number_generated', array($this, 'before_invoice_number_generated'));
		Events::register('invoice_number_generated', array($this, 'invoice_number_generated'));
	}
	
	public function before_invoice_number_generated() {
		// Removes all variables and dash that is used in the custom invoice pattern
		
		return preg_replace(
			'/{yyyy}|{yy}|{mmm}|{mm}|{dd}|{d}|{num}|{num2}|{num3}|{num4}|-|/',
			'',
			$this->get($this->_module_detect())
		);
	}
	
	public function invoice_number_generated($count) {
		/**
		 * $count is passed by invoices_m which is the "real" invoice number
		 */
		 
		if (empty($count)) {
			// Looks like this is the first invoice, let's use invoice number 1
			
			$count = 1;
		}
		
		// Variables we can use in the pattern
		
		$data = array(
			'yyyy'		=> date('Y'),
			'yy'		=> date('y'),
			'mmm'		=> date('M'),
			'mm'		=> date('m'),
			'dd'		=> date('d'),
			'd'			=> date('j'),
			'num'		=> $count,
			'num2'		=> str_pad($count, 2, '0', STR_PAD_LEFT),
			'num3'		=> str_pad($count, 3, '0', STR_PAD_LEFT),
			'num4'		=> str_pad($count, 4, '0', STR_PAD_LEFT),
		);
		
		$this->ci->load->library('parser');
		
		$invoice_string = $this->parser->parse_string($this->get($this->_module_detect()), $data, TRUE);
		
		// Return the custom invoice number back to invoices_m
		
		return $invoice_string;
	}
	
	/**
	 * Detect current module from the controller to support Estimates and
	 * Credit Notes
	 * 
	 * @return string
	 */
	private function _module_detect()
	{
		$pattern = 'invoice_pattern';

		if (($this->template->module == 'invoices' and substr($this->uri->uri_string(), 6, strlen('estimates')) == 'estimates') or ( $this->template->module == 'estimates'))
		{
			$pattern = 'estimate_pattern';
		}
		elseif (($this->template->module == 'invoices' and substr($this->uri->uri_string(), 6, strlen('credit_notes')) == 'credit_notes') or ( $this->template->module == 'credit_notes'))
		{
			$pattern = 'credit_note_pattern';
		}
		
		return $pattern;
	}

}


/* End of file */
