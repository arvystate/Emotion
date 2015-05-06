				<div id="sidebar">
					<div class="box_contact_r">
                        <div class="box_contact_t">
                            <div class="box_contact_b">
                                <div class="box_contact">
                                	<h5>Create package</h5>
                                    
                                	<ul class="wizard">
                                    	<?php
										if ($mode == 'update')
										{
											$step = 0;
										}
										
										$links = array ('General', 'License', 'Features', 'Configuration', 'Options', 'Review', 'Download');
										$urls = array ('general', 'license', 'feature', 'config', 'options', 'review', 'download');
								
										for ($i = 0; $i < count ($links); $i++)
										{
											if ($mode == $urls[$i])
											{
												echo ('<li class="selected"><h6><strong>' . $links[$i] . '</strong></h6></li>');
											}
											else if ($step > $i)
											{
												// Downloading, not allowed to go back, only to review.
												if ( ($step == 6) && ($i != 5) )
												{
													echo ('<li class="checked"><h6>' . $links[$i] . '</h6></li>');
												}
												else
												{
													echo ('<li class="checked"><h6><a href="/create/wizard/' . $urls[$i] . '/">' . $links[$i] . '</a></h6></li>');
												}
											}
											else
											{
												if ($i == $step)
												{
													echo ('<li><h6><a href="/create/wizard/' . $urls[$i] . '/">' . $links[$i] . '</a></h6></li>');
												}
												else
												{
													echo ('<li><h6>' . $links[$i] . '</h6></li>');
												}
											}											
										}
										
										?>
                                    </ul>
                                </div>
                            </div>
                        </div>
					</div><!-- end .box_contact_r -->
				</div><!-- end #sidebar -->

			</div><!-- end #main_content -->