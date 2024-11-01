<section id="alr_content1" class="alr_tab_section">
    <div class="tab_inner_container">
        <form method="post" class="alr_setting" id="alr_setting_tab_form" enctype="multipart/form-data">
			<div class="accordion_set">
                <div class="accordion heading add-sma-option">
                    <label>
                        <?php esc_html_e( 'General Setting', 'woocommerce-ajax-login-register' ); ?> 
                        <span class="ast-accordion-btn">
                            <div class="spinner workflow_spinner" style="float:left"></div>
                            <button name="save" class="alt-btn button-primary woocommerce-save-button" type="submit" value="Save changes" >
                                <?php esc_html_e( 'Save Changes', 'woocommerce-ajax-login-register' ); ?>
                            </button>
                            <?php wp_nonce_field( 'alr_setting_form_action', 'alr_setting_form_nonce_field' ); ?>
                            <input type="hidden" name="action" value="alr_setting_form_update">
                        </span>	
                    </label>
                </div>
                <div class="panel options add-alr-option">
                    <div class="table_border">
                            <?php $this->get_html( $this->get_settings() ); ?>
                       
                    </div>
                </div>
			</div>
        </form> 
    </div>
</section>