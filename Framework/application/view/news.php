			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>News</h1>
                        <h4>Follow updates and notifications from Emotion Engine development team.</h4>
                        
                        <br />
                        <?php
									
						if (count ($news) == 0)
						{
							echo ('There are currently no news.');
						}
						else
						{
							foreach ($news as $value)
							{
								echo ('<h6><strong>' . $value['title'] . '</strong></h6><br />');
								echo ($value['content']);
								echo ('<div class="news_post">Posted by ' . $value['full_name'] . ' on ' . date ("M j, Y", $value['timestamp']) . '</div>');
								echo ('<br />');
							}
						}
						?>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->
			
				<div id="sidebar">
					<div class="box_contact_r">
                        <div class="box_contact_t">
                            <div class="box_contact_b">
                                <div class="box_contact">
                                    <h5>Statistics</h5>
                                    
                                    
                                </div>
                            </div>
                        </div>
					</div><!-- end .box_contact_r -->
				</div><!-- end #sidebar -->

			</div><!-- end #main_content -->