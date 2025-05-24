<?php
$is_premium = cf7pa_fs()->can_use_premium_code__premium_only();
?>
<div class="cf7pa-container flex flex-col justify-center border-b border-gray-200 dark:border-gray-700">
	<header class="cf7pa-nav-bar">
		<ul class="flex">
			<li class="mr-2">
				<a href="#settings" class="cf7pa-nav-item active group" aria-current="page">
					<svg fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path clip-rule="evenodd" fill-rule="evenodd" d="M8.34 1.804A1 1 0 019.32 1h1.36a1 1 0 01.98.804l.295 1.473c.497.144.971.342 1.416.587l1.25-.834a1 1 0 011.262.125l.962.962a1 1 0 01.125 1.262l-.834 1.25c.245.445.443.919.587 1.416l1.473.294a1 1 0 01.804.98v1.361a1 1 0 01-.804.98l-1.473.295a6.95 6.95 0 01-.587 1.416l.834 1.25a1 1 0 01-.125 1.262l-.962.962a1 1 0 01-1.262.125l-1.25-.834a6.953 6.953 0 01-1.416.587l-.294 1.473a1 1 0 01-.98.804H9.32a1 1 0 01-.98-.804l-.295-1.473a6.957 6.957 0 01-1.416-.587l-1.25.834a1 1 0 01-1.262-.125l-.962-.962a1 1 0 01-.125-1.262l.834-1.25a6.957 6.957 0 01-.587-1.416l-1.473-.294A1 1 0 011 10.68V9.32a1 1 0 01.804-.98l1.473-.295c.144-.497.342-.971.587-1.416l-.834-1.25a1 1 0 01.125-1.262l.962-.962A1 1 0 015.38 3.03l1.25.834a6.957 6.957 0 011.416-.587l.294-1.473zM13 10a3 3 0 11-6 0 3 3 0 016 0z"></path>
					</svg>
					Settings
				</a>
			</li>
			<li class="mr-2">
				<a href="#emails" class="cf7pa-nav-item group <?php echo !$is_premium ? 'disabled' : ''; ?>" <?php echo !$is_premium ? 'onclick="return false;"' : ''; ?>>
					<svg style="width: 18px" class="w-[18px] h-[18px]" fill="currentColor" viewBox="0 0 20 16" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
						<path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z" />
						<path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z" />
					</svg>
					Emails (Pro)
				</a>
			</li>
		</ul>
    <?php if (!cf7pa_fs()->can_use_premium_code()) { ?>
      <div class="inline-flex items-center">
        <a class="cf7pa-button-link" href="<?php echo cf7pa_fs()->get_trial_url(); ?>" rel="external noreferrer noopener">Start free trial</a>
      </div>
    <?php } ?>
	</header>

	<div class="cf7pa-tabs">
		<?php
		require_once CF7PA_ADDONS_PATH . '/includes/admin/templates/settings.php';

		if ( $is_premium ) {
			require_once CF7PA_ADDONS_PATH . '/includes/admin/templates/emails.php';
		}
		?>
	</div>
</div>