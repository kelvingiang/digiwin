   <div id="back-top-wrapper">
       <a id="back-top">
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
               <path d="M297.4 169.4C309.9 156.9 330.2 156.9 342.7 169.4L534.7 361.4C547.2 373.9 547.2 394.2 534.7 406.7C522.2 419.2 501.9 419.2 489.4 406.7L320 237.3L150.6 406.6C138.1 419.1 117.8 419.1 105.3 406.6C92.8 394.1 92.8 373.8 105.3 361.3L297.3 169.3z" />
           </svg>
       </a>
   </div>
   </main>

   <?php if (!is_page('about')) {
        $lang = dgw_get_lang();
    ?>

       <footer id="footer">
           <div class="footer-space">
               <div>
                   <h3><?php _e('Office') ?> </h3>
                   <ul class='footer-list'>
                       <li><label><?php echo get_post_meta(1, '_info_address_' . $lang, true) ?></label>
                       </li>
                   </ul>
               </div>
               <div>
                   <h3><?php _e('Contact Us') ?> </h3>
                   <ul class='footer-list'>
                       <li> <label> <strong> Phone :</strong> <?php echo get_post_meta(1, '_info_phone', true) ?></label></li>
                       <li><label><strong> Fax :</strong> <?php echo get_post_meta(1, '_info_fax', true) ?></label></li>
                       <li><label><strong>Email :</strong> <?php echo get_post_meta(1, '_info_email', true) ?></label></li>
                   </ul>
               </div>
               <div>
                   <h3><?php _e('link') ?> </h3>
                   <ul class='footer-list'>
                       <li>
                           <a href="<?php echo $lang === 'vn'
                                        ? 'https://www.facebook.com/Digiwinsoftvn'
                                        : 'https://www.facebook.com/Digiwinsoftware'; ?>"
                               target="_blank">Facebook</a>
                           | <a href="https://lin.ee/80E5J8d" target="_blank"> Line </a>
                           | <a href="https://zalo.me/2873315813915643766" target="_blank"> Zalo </a>
                           | <a href="https://www.youtube.com/channel/UC5wPn6YNU6KHkrgAjCIojVA/?sub_confirmation=1"
                               target="_blank"> Youtube </a>
                           | <a href="https://www.linkedin.com/company/digiwinsoft-asean/" target="_blank"> Linkedin </a>
                       </li>
                       <li>
                           <a href="https://digiwin.com.my" target="_blank"> Digiwinsoft Malaysia </a>
                       </li>
                       <li>
                           <a href="https://digiwin.co.th" target="_blank"> Digiwinsoft Thailand</a>
                       </li>

                   </ul>
               </div>
           </div>
           <div class="copy-right">
               <p>&copy; <?php echo esc_html(date_i18n(__('Y', 'blankslate'))); ?>
                   <?php echo esc_html(get_bloginfo('name')); ?></p>
           </div>
       </footer>
   <?php } ?>
   <!-- </div> -->