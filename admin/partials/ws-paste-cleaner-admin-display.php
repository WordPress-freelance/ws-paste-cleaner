<?php
/**
 * Admin settings page for WS Paste Cleaner.
 *
 * @link       https://wordpress-freelance.com
 * @since      1.0.0
 *
 * @package    WS_Paste_Cleaner
 * @subpackage WS_Paste_Cleaner/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$level = get_option( 'ws_paste_cleaner_level', 'moderate' );
$auto  = (bool) get_option( 'ws_paste_cleaner_auto', 1 );
$stats = (int) get_option( 'ws_paste_cleaner_stats', 0 );
$saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
?>

<div class="ws-paste-cleaner-wrap">

	<div class="ws-header">
		<div class="ws-logo">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/logo.png' ); ?>" alt="WebStrategy" class="ws-logo-img">
			<span class="ws-title"><?php esc_html_e( 'WebStrategy', 'ws-paste-cleaner' ); ?></span>
		</div>
		<h1 class="ws-page-title">
			<?php esc_html_e( 'WS Paste Cleaner', 'ws-paste-cleaner' ); ?>
			<span><?php esc_html_e( 'Settings', 'ws-paste-cleaner' ); ?></span>
		</h1>
	</div>

	<?php if ( $saved ) : ?>
		<div class="ws-notice ws-notice-success">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
			<?php esc_html_e( 'Settings saved.', 'ws-paste-cleaner' ); ?>
		</div>
	<?php endif; ?>

	<div class="ws-grid">

		<div class="ws-main">

			<div class="ws-card">
				<div class="ws-card-head">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
					<?php esc_html_e( 'About this plugin', 'ws-paste-cleaner' ); ?>
				</div>
				<div class="ws-card-body">
					<p>
						<?php
						echo wp_kses(
							__( 'WS Paste Cleaner intercepts paste events from Microsoft Word and strips the parasitic HTML — <code>MsoNormal</code> classes, inline styles, proprietary Office namespaces.', 'ws-paste-cleaner' ),
							array( 'code' => array() )
						);
						?>
					</p>
					<p>
						<?php
						echo wp_kses(
							__( 'Compatible with <strong>Gutenberg</strong> and the <strong>Classic Editor</strong> (TinyMCE).', 'ws-paste-cleaner' ),
							array( 'strong' => array() )
						);
						?>
					</p>
				</div>
			</div>

			<div class="ws-card ws-card-accent">
				<div class="ws-card-head">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg>
					<?php esc_html_e( 'Usage statistics', 'ws-paste-cleaner' ); ?>
				</div>
				<div class="ws-card-body">
					<div class="ws-stat">
						<div class="ws-stat-label"><?php esc_html_e( 'Cleanups performed', 'ws-paste-cleaner' ); ?></div>
						<div class="ws-stat-value"><?php echo esc_html( number_format_i18n( $stats ) ); ?></div>
					</div>
					<p class="ws-stat-note"><?php esc_html_e( 'Since plugin activation.', 'ws-paste-cleaner' ); ?></p>
				</div>
			</div>

			<div class="ws-card">
				<div class="ws-card-head">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>
					<?php esc_html_e( 'Configuration', 'ws-paste-cleaner' ); ?>
				</div>
				<div class="ws-card-body">

					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'ws_paste_cleaner_settings' ); ?>
						<input type="hidden" name="action" value="ws_paste_cleaner_save">

						<div class="ws-form-section">
							<h3 class="ws-form-section-title"><?php esc_html_e( 'Automatic activation', 'ws-paste-cleaner' ); ?></h3>

							<label class="ws-toggle">
								<input type="checkbox" name="ws_paste_cleaner_auto" value="1" <?php checked( $auto ); ?>>
								<span class="ws-toggle-slider"></span>
								<span class="ws-toggle-label"><?php esc_html_e( 'Clean automatically on paste', 'ws-paste-cleaner' ); ?></span>
							</label>
							<p class="ws-help"><?php esc_html_e( 'When disabled, no cleaning happens until you paste again with this option re-enabled.', 'ws-paste-cleaner' ); ?></p>
						</div>

						<div class="ws-form-section">
							<h3 class="ws-form-section-title"><?php esc_html_e( 'Cleaning level', 'ws-paste-cleaner' ); ?></h3>

							<div class="ws-radio-group">

								<label class="ws-radio">
									<input type="radio" name="ws_paste_cleaner_level" value="light" <?php checked( $level, 'light' ); ?>>
									<span class="ws-radio-mark"></span>
									<div class="ws-radio-content">
										<strong><?php esc_html_e( 'Light', 'ws-paste-cleaner' ); ?></strong>
										<p><?php esc_html_e( 'Strips Word-only metadata (Mso classes, Office namespaces, conditional comments). Keeps the rest of the structure intact.', 'ws-paste-cleaner' ); ?></p>
									</div>
								</label>

								<label class="ws-radio">
									<input type="radio" name="ws_paste_cleaner_level" value="moderate" <?php checked( $level, 'moderate' ); ?>>
									<span class="ws-radio-mark"></span>
									<div class="ws-radio-content">
										<strong><?php esc_html_e( 'Moderate', 'ws-paste-cleaner' ); ?></strong>
										<span class="ws-badge"><?php esc_html_e( 'Recommended', 'ws-paste-cleaner' ); ?></span>
										<p><?php esc_html_e( 'Strips all Word markup (classes, inline styles, span/font wrappers) while keeping semantic structure: headings, lists, links, strong/em.', 'ws-paste-cleaner' ); ?></p>
									</div>
								</label>

								<label class="ws-radio">
									<input type="radio" name="ws_paste_cleaner_level" value="aggressive" <?php checked( $level, 'aggressive' ); ?>>
									<span class="ws-radio-mark"></span>
									<div class="ws-radio-content">
										<strong><?php esc_html_e( 'Aggressive', 'ws-paste-cleaner' ); ?></strong>
										<p><?php esc_html_e( 'Plain text only. Removes all HTML and rebuilds the content as paragraphs.', 'ws-paste-cleaner' ); ?></p>
									</div>
								</label>

							</div>
						</div>

						<button type="submit" class="ws-btn ws-btn-primary">
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
							<?php esc_html_e( 'Save settings', 'ws-paste-cleaner' ); ?>
						</button>
					</form>

				</div>
			</div>

			<div class="ws-card">
				<div class="ws-card-head">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
					<?php esc_html_e( 'Test zone', 'ws-paste-cleaner' ); ?>
				</div>
				<div class="ws-card-body">
					<p class="ws-help"><?php esc_html_e( 'Paste Word content below to preview how it will be cleaned.', 'ws-paste-cleaner' ); ?></p>

					<div class="ws-test-grid">
						<div class="ws-test-col">
							<label class="ws-test-label" for="ws-test-input"><?php esc_html_e( 'Word content (before)', 'ws-paste-cleaner' ); ?></label>
							<textarea id="ws-test-input" class="ws-test-textarea" placeholder="<?php esc_attr_e( 'Paste your Word content here…', 'ws-paste-cleaner' ); ?>"></textarea>
						</div>
						<div class="ws-test-col">
							<label class="ws-test-label" for="ws-test-output"><?php esc_html_e( 'Cleaned result (after)', 'ws-paste-cleaner' ); ?></label>
							<textarea id="ws-test-output" class="ws-test-textarea" readonly></textarea>
						</div>
					</div>

					<button type="button" id="ws-test-clean-btn" class="ws-btn ws-btn-secondary">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
						<?php esc_html_e( 'Run test cleanup', 'ws-paste-cleaner' ); ?>
					</button>
				</div>
			</div>

		</div>

		<div class="ws-sidebar">

			<div class="ws-sidebar-card ws-sidebar-card-accent">
				<div class="ws-sidebar-head">
					<div class="ws-sidebar-info">
						<div class="ws-sidebar-title"><?php esc_html_e( 'WS Paste Cleaner', 'ws-paste-cleaner' ); ?></div>
						<div class="ws-sidebar-sub">v<?php echo esc_html( WS_PASTE_CLEANER_VERSION ); ?></div>
					</div>
				</div>
				<div class="ws-sidebar-body">
					<span class="ws-pill"><?php esc_html_e( 'Free plugin', 'ws-paste-cleaner' ); ?></span>
					<p><?php esc_html_e( 'Automatic Microsoft Word cleanup on paste in WordPress.', 'ws-paste-cleaner' ); ?></p>
				</div>
			</div>

			<div class="ws-sidebar-card">
				<div class="ws-sidebar-head">
					<div class="ws-sidebar-head-title"><?php esc_html_e( 'How to use', 'ws-paste-cleaner' ); ?></div>
				</div>
				<div class="ws-sidebar-body">
					<ol class="ws-steps">
						<li><?php esc_html_e( 'Enable automatic cleaning above.', 'ws-paste-cleaner' ); ?></li>
						<li><?php esc_html_e( 'Pick your cleaning level.', 'ws-paste-cleaner' ); ?></li>
						<li><?php esc_html_e( 'Copy content from Microsoft Word.', 'ws-paste-cleaner' ); ?></li>
						<li><?php esc_html_e( 'Paste into the WordPress editor — cleanup is automatic.', 'ws-paste-cleaner' ); ?></li>
					</ol>
				</div>
			</div>

			<div class="ws-sidebar-card">
				<div class="ws-sidebar-body" style="padding-bottom:0;">
					<span class="ws-pill"><?php esc_html_e( 'Support &amp; resources', 'ws-paste-cleaner' ); ?></span>
				</div>
				<div class="ws-sidebar-links">
					<a class="ws-sidebar-link" href="https://wordpress.org/support/plugin/ws-paste-cleaner/" target="_blank" rel="noopener">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s-8-4.5-8-11.8A6.2 6.2 0 0112 4a6.2 6.2 0 018 6.2C20 17.5 12 22 12 22z"/></svg>
						<?php esc_html_e( 'Support forum', 'ws-paste-cleaner' ); ?>
					</a>
					<a class="ws-sidebar-link" href="https://wordpress-freelance.com" target="_blank" rel="noopener">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
						<?php esc_html_e( 'WebStrategy website', 'ws-paste-cleaner' ); ?>
					</a>
				</div>
			</div>

		</div>

	</div>
</div>
