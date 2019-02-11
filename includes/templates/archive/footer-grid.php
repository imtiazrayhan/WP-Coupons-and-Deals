<?php
/*
 * Footer Grid for Templates
 */
if ( $parent == 'footer' || $parent == 'headerANDfooter' ): ?>

    <div id="wpcd_coupon_pagination_wr" class="wpcd_coupon_pagination wpcd_clearfix">
        
        <?php
            
            $add_args = array();
            
            if ( isset( $_POST['wpcd_category'] ) && !empty( $_POST['wpcd_category'] ) ) {
                $add_args['wpcd_category'] = sanitize_text_field( $_POST['wpcd_category'] );
            }

            if ( isset($_POST['page_num'] ) && !empty( $_POST['page_num'] ) ) {
                $current = intval( $_POST['page_num'] );
            } else {
                $current = 1;
            }

            if( isset( $_POST['search_text'] ) && ! empty( $_POST['search_text'] ) ) {
                $add_args['search_text'] = sanitize_text_field( $_POST['search_text'] );
            }
        
            echo paginate_links( 
                array(
                    'base'      => '?page_num=%#%',
                    'format'    => '?page=%#%',
                    'add_args'  => $add_args,
                    'current'   => $current,
                    'total'     => $max_num_page,
                    'prev_next' => true,
                    'prev_text' => __( '« Prev', 'wpcd-coupon' ),
                    'next_text' => __( 'Next »', 'wpcd-coupon' ),
                )
            );
            
        ?>
    </div>

    <?php if ( !isset( $_POST['action'] ) || $_POST['action'] != 'wpcd_coupons_category_action' ): ?>
            </div> <!-- wpcd_coupon_archive_container -->
        </div> <!-- wpcd_coupon_archive_container_main -->
    </ul> <!-- wpcd_coupon_ul -->
    <?php endif; ?>

</section>
<?php endif; ?>