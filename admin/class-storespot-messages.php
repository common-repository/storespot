<?php

class StoreSpot_Messages {

	public function activation_notice() {

		if( get_transient( 'stsp-admin-activation-notice' ) ){ ?>

				<div class="notice notice-info is-dismissible">
					<table border="0" style="padding: 7px 0;">
						<tr><td>
							<img src="<?= plugin_dir_url( dirname( __FILE__ ) ) . 'img/logo.png' ?>" style="padding-top:6px;" />
						</td><td style="padding:0 0 15px 15px;">
							<p style="font-size:15px;">
								Thank you for installing <strong>StoreSpot</strong>!<br />
								Go back to StoreSpot to complete the connection.
							</p>
							<a href="https://app.storespot.io" target="_blank" class="button button-primary">
								Go to StoreSpot
							</a>
							<a href="https://app.storespot.io/register?ref=wpplug" target="_blank" class="button" style="margin-left:10px;">
								I don't have an account yet
							</a>
						</td></tr>
					</table>


				</div>

				<?php
				/* Delete transient, only display this notice once. */
				delete_transient( 'stsp-admin-activation-notice' );
		}

	}
}
