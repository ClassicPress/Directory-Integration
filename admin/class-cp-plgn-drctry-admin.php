<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.tukutoi.com/
 * @since      1.0.0
 *
 * @package    Cp_Plgn_Drctry
 * @subpackage Cp_Plgn_Drctry/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks to
 * enqueue the admin-facing stylesheet and JavaScript.
 * As you add hooks and methods, update this description.
 *
 * @package    Cp_Plgn_Drctry
 * @subpackage Cp_Plgn_Drctry/admin
 * @author     bedas <hello@tukutoi.com>
 */
class Cp_Plgn_Drctry_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The unique prefix of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_prefix    The string used to uniquely prefix technical functions of this plugin.
	 */
	private $plugin_prefix;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The CP Dir Class instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $cp_dir    The Class reference for the ClassicPress Plugin Directory.
	 */
	private $cp_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $plugin_prefix    The unique prefix of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_prefix, $version ) {

		$this->plugin_name   = $plugin_name;
		$this->plugin_prefix = $plugin_prefix;
		$this->version = $version;
		$this->cp_dir = new Cp_Plgn_Drctry_Cp_Dir( $plugin_name, $plugin_prefix, $version );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {

		if ( 'plugins_page_cp-plugins' === $hook_suffix ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cp-plgn-drctry-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( 'plugins_page_cp-plugins' === $hook_suffix ) {
			add_thickbox();
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cp-plgn-drctry-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'ajax_object',
				array(
					'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'admin_url' => esc_url( get_admin_url( null, '', 'admin' ) ),
					'nonce' => wp_create_nonce( 'cp-plgn-drctry-nonce' ),
				)
			);
		}
	}

	/**
	 * Install a Plugin.
	 */
	public function install_cp_plugin() {

		if ( ! isset( $_POST['nonce'] )
			|| empty( $_POST['nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cp-plgn-drctry-nonce' ) ) {
			 die( 'Invalid or missing Nonce!' );
		}

		if ( ! isset( $_POST['url'] ) ) {
			wp_send_json( 'Something went wrong' );
		}

		/**
		 * We include Upgrader Class.
		 *
		 * @todo Check this path on EACH CP UPDATE. It might change!
		 */
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		$upgrader = new Plugin_Upgrader();
		$response = $upgrader->install( esc_url_raw( wp_unslash( $_POST['url'] ) ) );

		wp_send_json( $response );

	}

	/**
	 * Creates the submenu item and calls on the Submenu Page object to render
	 * the actual contents of the page.
	 */
	public function add_plugins_list() {

		add_submenu_page(
			'plugins.php',
			esc_html__( 'ClassicPress Plugins', 'cp-plgn-drctry' ),
			esc_html__( 'Add CP Plugin', 'cp-plgn-drctry' ),
			'install_plugins',
			'cp-plugins',
			array( $this, 'render' ),
		);

	}

	/**
	 * Render the admin page.
	 */
	public function render() {
		?>
		<div id="loadingDiv" style="display:none"><span class="spinner"></span></div>
		<div class="wrap">
			<h1><?php esc_html_e( 'ClassicPress Plugins', 'cp-plgn-drctry' ); ?></h1>
			<p><?php esc_html_e( 'Browse, Install and Activate ClassicPress Plugins', 'cp-plgn-drctry' ); ?></p>
			<div class="error" style="display:none;"></div>
			<?php $this->cp_dir->list_plugins(); ?>
		</div>
		<?php
	}

}