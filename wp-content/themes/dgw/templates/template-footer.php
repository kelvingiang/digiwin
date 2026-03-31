   <div id="back-top-wrapper">
       <a id="back-top">
           <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
               <path d="M297.4 169.4C309.9 156.9 330.2 156.9 342.7 169.4L534.7 361.4C547.2 373.9 547.2 394.2 534.7 406.7C522.2 419.2 501.9 419.2 489.4 406.7L320 237.3L150.6 406.6C138.1 419.1 117.8 419.1 105.3 406.6C92.8 394.1 92.8 373.8 105.3 361.3L297.3 169.3z" />
           </svg> -->
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
               <path d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM331.3 188.7L435.3 292.7C439.9 297.3 441.2 304.2 438.8 310.1C436.4 316 430.5 320 424 320L368 320L368 416C368 433.7 353.7 448 336 448L304 448C286.3 448 272 433.7 272 416L272 320L216 320C209.5 320 203.7 316.1 201.2 310.1C198.7 304.1 200.1 297.2 204.7 292.7L308.7 188.7C314.9 182.5 325.1 182.5 331.3 188.7z" />
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
                   <div>
                       <img class="company-logo"
                           title="digiwin company"
                           alt="digiwin company"
                           src="<?php echo PART_IMAGES . 'logo-white-Digiwin.png' ?>"
                           width="150"
                           height="42" />
                   </div>
                   <div class="foot-slogan">
                       DIGITAL TRANSFORMATION PARTNER
                   </div>
                   <h3><?php _e('Office In VietNam') ?> </h3>
                   <ul class='footer-list'>
                       <li><label><?php echo get_post_meta(1, '_info_address_' . $lang, true) ?></label>
                       </li>
                   </ul>
                   <div class="prize-link">
                       <?php
                        // Giá trị mặc định
                        $link_award = WP_HOME . '/giai-thuong-danh-hieu-chung-nhan-quoc-te-cua-digiwin'; // fallback link

                        $allowed_langs = ['vn', 'cn'];
                        $lang = isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : '';

                        // Chỉ xử lý nếu giá trị hợp lệ
                        if (in_array($lang, $allowed_langs, true)) {
                            if ($lang === 'vn') {
                                $link_award = WP_HOME . '/giai-thuong-danh-hieu-chung-nhan-quoc-te-cua-digiwin';
                            } elseif ($lang === 'cn') {
                                $link_award = WP_HOME . '/giai-thuong-danh-hieu-chung-nhan-quoc-te-cua-digiwin';
                            }
                        }
                        ?>
                       <a href="<?php echo $link_award;  ?>">
                           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                               <path d="M341.9 38.1C328.5 29.9 311.6 29.9 298.2 38.1C273.8 53 258.7 57 230.1 56.4C214.4 56 199.8 64.5 192.2 78.3C178.5 103.4 167.4 114.5 142.3 128.2C128.5 135.7 120.1 150.4 120.4 166.1C121.1 194.7 117 209.8 102.1 234.2C93.9 247.6 93.9 264.5 102.1 277.9C117 302.3 121 317.4 120.4 346C120 361.7 128.5 376.3 142.3 383.9C164.4 396 175.6 406 187.4 425.4L138.7 522.5C132.8 534.4 137.6 548.8 149.4 554.7L235.4 597.7C246.9 603.4 260.9 599.1 267.1 587.9L319.9 492.8L372.7 587.9C378.9 599.1 392.9 603.5 404.4 597.7L490.4 554.7C502.3 548.8 507.1 534.4 501.1 522.5L452.5 425.3C464.2 405.9 475.5 395.9 497.6 383.8C511.4 376.3 519.8 361.6 519.5 345.9C518.8 317.3 522.9 302.2 537.8 277.8C546 264.4 546 247.5 537.8 234.1C522.9 209.7 518.9 194.6 519.5 166C519.9 150.3 511.4 135.7 497.6 128.1C472.5 114.4 461.4 103.3 447.7 78.2C440.2 64.4 425.5 56 409.8 56.3C381.2 57 366.1 52.9 341.7 38zM320 160C373 160 416 203 416 256C416 309 373 352 320 352C267 352 224 309 224 256C224 203 267 160 320 160z" />
                           </svg>
                           <?php _e('Awards & Certificates') ?>
                       </a>

                   </div>
                   <div class="download-link">
                       <label class="download-title"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                               <path d="M352 96C352 78.3 337.7 64 320 64C302.3 64 288 78.3 288 96L288 306.7L246.6 265.3C234.1 252.8 213.8 252.8 201.3 265.3C188.8 277.8 188.8 298.1 201.3 310.6L297.3 406.6C309.8 419.1 330.1 419.1 342.6 406.6L438.6 310.6C451.1 298.1 451.1 277.8 438.6 265.3C426.1 252.8 405.8 252.8 393.3 265.3L352 306.7L352 96zM160 384C124.7 384 96 412.7 96 448L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 448C544 412.7 515.3 384 480 384L433.1 384L376.5 440.6C345.3 471.8 294.6 471.8 263.4 440.6L206.9 384L160 384zM464 440C477.3 440 488 450.7 488 464C488 477.3 477.3 488 464 488C450.7 488 440 477.3 440 464C440 450.7 450.7 440 464 440z" />
                           </svg><?php _e('Free Resources for Businesses') ?></label>

                       <a href="<?php echo WP_HOME . '/resource/cate/104/tag/' ?>">
                           <div class="download-btn">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                   <path d="M128 64C92.7 64 64 92.7 64 128L64 512C64 547.3 92.7 576 128 576L208 576L208 464C208 428.7 236.7 400 272 400L448 400L448 234.5C448 217.5 441.3 201.2 429.3 189.2L322.7 82.7C310.7 70.7 294.5 64 277.5 64L128 64zM389.5 240L296 240C282.7 240 272 229.3 272 216L272 122.5L389.5 240zM272 444C261 444 252 453 252 464L252 592C252 603 261 612 272 612C283 612 292 603 292 592L292 564L304 564C337.1 564 364 537.1 364 504C364 470.9 337.1 444 304 444L272 444zM304 524L292 524L292 484L304 484C315 484 324 493 324 504C324 515 315 524 304 524zM400 444C389 444 380 453 380 464L380 592C380 603 389 612 400 612L432 612C460.7 612 484 588.7 484 560L484 496C484 467.3 460.7 444 432 444L400 444zM420 572L420 484L432 484C438.6 484 444 489.4 444 496L444 560C444 566.6 438.6 572 432 572L420 572zM508 464L508 592C508 603 517 612 528 612C539 612 548 603 548 592L548 548L576 548C587 548 596 539 596 528C596 517 587 508 576 508L548 508L548 484L576 484C587 484 596 475 596 464C596 453 587 444 576 444L528 444C517 444 508 453 508 464z" />
                               </svg>
                               <?php _e('Download 2026 Digital Transformation Whitepaper') ?>
                           </div>
                       </a>
                   </div>
               </div>

               <div>
                   <h3 class="dot-title"><?php _e('Solutions & Industries') ?> </h3>
                   <ul class='footer-list article-link'>
                       <li>
                           <a href="<?php echo WP_HOME . '/resource/cate/104/tag/' ?>">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z" />
                               </svg>
                               <?php _e('System Administration (ERP)') ?>
                           </a>
                       </li>
                       <li>
                           <a href="<?php echo WP_HOME . '/solution/cate/54/tag/' ?>">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z" />
                               </svg>
                               <?php _e('Production Management (MES)') ?>
                           </a>
                       </li>
                       <li>
                           <a href="<?php echo WP_HOME . '/solutions/he-thong-logistics-thong-minh/' ?>">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z" />
                               </svg>
                               <?php _e('Smart Warehouse (WMS)') ?>
                           </a>
                       </li>
                       <li>
                           <a href="<?php echo WP_HOME . '/solutions/nganh-cao-su-nhua/' ?>">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z" />
                               </svg>
                               <?php _e('Digital Transformation in the Plastics Industry') ?>
                           </a>
                       </li>
                       <li>
                           <a href="<?php echo WP_HOME . '/cases/' ?>">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M439.1 297.4C451.6 309.9 451.6 330.2 439.1 342.7L279.1 502.7C266.6 515.2 246.3 515.2 233.8 502.7C221.3 490.2 221.3 469.9 233.8 457.4L371.2 320L233.9 182.6C221.4 170.1 221.4 149.8 233.9 137.3C246.4 124.8 266.7 124.8 279.2 137.3L439.2 297.3z" />
                               </svg>
                               <?php _e('Case Studies') ?>
                           </a>
                       </li>

                   </ul>
               </div>

               <div>
                   <h3 class="dot-title"><?php _e('contact') ?> </h3>
                   <ul class='footer-list contact-list'>
                       <li>
                           <div>
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M224.2 89C216.3 70.1 195.7 60.1 176.1 65.4L170.6 66.9C106 84.5 50.8 147.1 66.9 223.3C104 398.3 241.7 536 416.7 573.1C493 589.3 555.5 534 573.1 469.4L574.6 463.9C580 444.2 569.9 423.6 551.1 415.8L453.8 375.3C437.3 368.4 418.2 373.2 406.8 387.1L368.2 434.3C297.9 399.4 241.3 341 208.8 269.3L253 233.3C266.9 222 271.6 202.9 264.8 186.3L224.2 89z" />
                               </svg>
                           </div>
                           <div>
                               <label>
                                   Hotline<br>
                                   <strong><?php echo get_post_meta(1, '_info_phone', true) ?></strong>
                               </label>
                           </div>
                       </li>

                       <li>
                           <div>
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M112 128C85.5 128 64 149.5 64 176C64 191.1 71.1 205.3 83.2 214.4L291.2 370.4C308.3 383.2 331.7 383.2 348.8 370.4L556.8 214.4C568.9 205.3 576 191.1 576 176C576 149.5 554.5 128 528 128L112 128zM64 260L64 448C64 483.3 92.7 512 128 512L512 512C547.3 512 576 483.3 576 448L576 260L377.6 408.8C343.5 434.4 296.5 434.4 262.4 408.8L64 260z" />
                               </svg>
                           </div>
                           <div>
                               <label>
                                   E-mail<br>
                                   <strong><?php echo get_post_meta(1, '_info_email', true) ?></strong>
                               </label>
                           </div>
                       </li>

                       <li>
                           <div>
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                   <path d="M320.2 112C435 112.1 528 205.2 528 320C528 342.1 524.6 363.4 518.2 383.4C516.2 383.8 514.1 384 512 384L509.3 384C500.8 384 492.7 380.6 486.7 374.6L457.4 345.3C451.4 339.3 448 331.2 448 322.7L448 272C448 263.2 455.2 256 464 256C472.8 256 480 248.8 480 240C480 231.2 472.8 224 464 224L440 224C426.7 224 416 234.7 416 248C416 261.3 405.3 272 392 272L336 272C327.2 272 320 279.2 320 288C320 296.8 312.8 304 304 304L278.6 304C266.1 304 256 293.9 256 281.4C256 275.4 258.4 269.6 262.6 265.4L332.7 195.3C334.8 193.2 336 190.3 336 187.3C336 181.1 330.9 176 324.7 176L310.6 176C298.1 176 288 165.9 288 153.4C288 147.4 290.4 141.6 294.6 137.4L317.7 114.3C318.5 113.5 319.3 112.8 320.2 112.1zM502.4 420.1C469.6 479.7 408.5 521.5 337.2 527.3C336.5 525 336.1 522.5 336.1 520C336.1 506.7 325.4 496 312.1 496L285.4 496C276.9 496 268.8 492.6 262.8 486.6L233.5 457.3C227.5 451.3 224.1 443.2 224.1 434.7L224.1 368C224.1 350.3 238.4 336 256.1 336L354.8 336C363.3 336 371.4 339.4 377.4 345.4L406.7 374.7C412.7 380.7 420.8 384.1 429.3 384.1L434.8 384.1C443.3 384.1 451.4 387.5 457.4 393.5L473.4 409.5C477.6 413.7 483.4 416.1 489.4 416.1C494.2 416.1 498.7 417.6 502.4 420.2zM320 576L346.2 574.7C337.6 575.6 328.9 576 320 576zM346.2 574.7C475.3 561.6 576 452.6 576 320C576 178.6 461.4 64 320 64L320 64C178.6 64 64 178.6 64 320C64 447.5 157.2 553.3 279.3 572.8C292.5 574.9 306.1 576 320 576zM251.3 187.3L219.3 219.3C213.1 225.5 202.9 225.5 196.7 219.3C190.5 213.1 190.5 202.9 196.7 196.7L228.7 164.7C234.9 158.5 245.1 158.5 251.3 164.7C257.5 170.9 257.5 181.1 251.3 187.3z" />
                               </svg>
                           </div>
                           <div>
                               <label>Website <br><strong>digiwin.com.vn</strong></label>
                           </div>
                       </li>
                   </ul>
               </div>



               <div class="social-link">
                   <h3><?php _e('Zalo OA Community') ?> </h3>
                   <ul class='footer-list'>
                       <li>
                           <img title="digiwin zalo" alt="digiwin zalo" width="100" height="100" src="<?php echo PART_IMAGES . 'zalo-qrcode.jpg'; ?>" />
                       </li>
                       <li class="zalo-text">
                           <?php _e('Scan to join Zalo OA & get the latest industry reports') ?>
                       </li>
                       <li class="icon-link">
                           <a href="<?php echo $lang === 'vn'
                                        ? 'https://www.facebook.com/Digiwinsoftvn'
                                        : 'https://www.facebook.com/Digiwinsoftware'; ?>" target="_blank">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L258.2 544L258.2 398.2L205.4 398.2L205.4 320L258.2 320L258.2 286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2L438.9 236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2L351.1 320L434.7 320L420.3 398.2L351 398.2L351 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96z" />
                               </svg></a>

                           <a href="https://www.youtube.com/channel/UC5wPn6YNU6KHkrgAjCIojVA/?sub_confirmation=1" target="_blank"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M581.7 188.1C575.5 164.4 556.9 145.8 533.4 139.5C490.9 128 320.1 128 320.1 128C320.1 128 149.3 128 106.7 139.5C83.2 145.8 64.7 164.4 58.4 188.1C47 231 47 320.4 47 320.4C47 320.4 47 409.8 58.4 452.7C64.7 476.3 83.2 494.2 106.7 500.5C149.3 512 320.1 512 320.1 512C320.1 512 490.9 512 533.5 500.5C557 494.2 575.5 476.3 581.8 452.7C593.2 409.8 593.2 320.4 593.2 320.4C593.2 320.4 593.2 231 581.8 188.1zM264.2 401.6L264.2 239.2L406.9 320.4L264.2 401.6z" />
                               </svg> </a>

                           <a href="https://www.linkedin.com/company/digiwinsoft-asean/" target="_blank">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                   <path d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM165 266.2L231.5 266.2L231.5 480L165 480L165 266.2zM236.7 198.5C236.7 219.8 219.5 237 198.2 237C176.9 237 159.7 219.8 159.7 198.5C159.7 177.2 176.9 160 198.2 160C219.5 160 236.7 177.2 236.7 198.5zM413.9 480L413.9 376C413.9 351.2 413.4 319.3 379.4 319.3C344.8 319.3 339.5 346.3 339.5 374.2L339.5 480L273.1 480L273.1 266.2L336.8 266.2L336.8 295.4L337.7 295.4C346.6 278.6 368.3 260.9 400.6 260.9C467.8 260.9 480.3 305.2 480.3 362.8L480.3 480L413.9 480z" />
                               </svg></a>
                       </li>


                   </ul>
               </div>

           </div>


           <div class="footer-icon">
               <div>
                   <img title="reddot winner" alt="reddot winner" width="30" height="35" src="<?php echo  PART_IMAGES . 'foot-icon-1.png' ?>" />
                   <label> REDDOT WINNER </label>
               </div>
               <div>
                   <img title="cmmi" alt="cmmi" width="30" height="35" src="<?php echo  PART_IMAGES . 'foot-icon-2.png' ?>" />
                   <label> CMMI <br><i> Level 4 </i></label>
               </div>
               <div>
                   <img title="muse design" alt="muse design" width="30" height="35" src="<?php echo  PART_IMAGES . 'foot-icon-3.png' ?>" />
                   <label> MUSE DESIGN <br><i> Awards </i></label>
                   </a>
               </div>
               <div>
                   <img title="Control Engineering" alt="Control Engineering" width="30" height="35" src="<?php echo  PART_IMAGES . 'foot-icon-4.png' ?>" />
                   <label> CONTROL ENGINEERING <br> <i>Product of the Year</i> </label>
               </div>
               <div>
                   <img title="isrs" alt="isrs" width="30" height="35" src="<?php echo  PART_IMAGES . 'foot-icon-5.png' ?>" />
                   <label> ISRS <br> <i>Reporting Standards</i> </label>
               </div>
           </div>

           <div class="copy-right">
               <div>&copy; 2026 DIGIWIN Vietnam. All rights reserved</div>

               <div><a class="foot-link" href="https://www.digiwin.com.vn/digiwinasean_privacy-policy/">Chính Sách Bảo Mật | Điều Kiện Sử Dụng </a></div>
           </div>
       </footer>
   <?php } ?>
   <!-- </div> -->