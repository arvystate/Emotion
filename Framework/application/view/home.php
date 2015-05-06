			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>Why Emotion Engine?</h1>
                        <h4>Designed espacilly for beginners in web development, <br />still has all functionality PHP professionals require.</h4>
                        
                        <ul>
                            <li>
                                <h6>Easy to use</h6>
                                <p>Very easy to use framework will allow you to learn quickly and migrate your old applications to optimize performance and use it's advanced features.</p>
                            </li>
                            <li>
                                <h6>Flexible</h6>
                                <p>Supporting both old procedural programming and new object-oriented Model-View-Controller pattern will let you build any kind of application.</p>
                            </li>
                            <li>
                            	<h6>Innovative</h6>
                                <p>One of the most innovative and perfected tools you can currently find on the internet. Creating possibilities are unlimited.</p>
                            </li>
                            <li>
                                <h6>Performance</h6>
                                <p>Fairly optimized and customized Emotion Engine framework is very fast compared to frameworks with slow bootstrap loaders and uses less memory.</p>
                            </li>
                            <li>
                                <h6>Powerful</h6>
                                <p>You are free to use any classes from larger library collection and most known database management systems.</p>
                            </li>
    
                        </ul>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->
			
				<div id="sidebar">
					<div class="box_contact_r">
                        <div class="box_contact_t">
                            <div class="box_contact_b">
                                <div class="box_contact">
                                    <h5>Emotion Engine News</h5>
                                    
                                    <?php
									
									if (count ($news) == 0)
									{
										echo ('There are currently no news.');
									}
									else
									{
										echo ('<ul>');
										
										foreach ($news as $value)
										{
											echo ('<li>');
											echo ('<div class="news_title">' . $value['title'] . '</div>');
                                            echo ('<div class="news_link"><a href="/news/' . $value['news_id'] . '/">Read More...</a></div>');
                                            echo ('<div class="news_post">Posted by ' . $value['full_name'] . ' on ' . date ("M j, Y", $value['timestamp']) . '</div>');
											echo ('</li>');
										}
										
										echo ('</ul>');
										
										if (intval ($newsCount[0]['count(*)']) > 4)
										{
											echo ('<a href="/news/">View Older News</a>');
										}
									}
									?>
                                </div>
                            </div>
                        </div>
					</div><!-- end .box_contact_r -->
				</div><!-- end #sidebar -->

			</div><!-- end #main_content -->