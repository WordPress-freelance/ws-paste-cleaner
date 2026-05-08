<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all actions and filters for the plugin.
 *
 * Maintains a list of all hooks that are registered throughout the plugin
 * and registers them with the WordPress API. Call the run function to
 * execute the list of actions and filters.
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/includes
 * @author     WebStrategy
 */
class WS_Paste_Cleaner_Loader {

	/**
	 * Actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $actions
	 */
	protected $actions;

	/**
	 * Filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $filters
	 */
	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress action.
	 * @param object $component     A reference to the instance.
	 * @param string $callback      The name of the callback method.
	 * @param int    $priority      Optional. Default 10.
	 * @param int    $accepted_args Optional. Default 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress filter.
	 * @param object $component     A reference to the instance.
	 * @param string $callback      The name of the callback method.
	 * @param int    $priority      Optional. Default 10.
	 * @param int    $accepted_args Optional. Default 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Utility function to register the actions and hooks into a single
	 * collection.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  array  $hooks
	 * @param  string $hook
	 * @param  object $component
	 * @param  string $callback
	 * @param  int    $priority
	 * @param  int    $accepted_args
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
