			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>Use Emotion Engine</h1>
                        <h4>Download Emotion Engine™ free of charge. Before you use the software you must read and fully understand the <a href="">license agreement</a>.</h4>
                        
                        <ul>
                        	<li>
                                <h6>Create your own package</h6>
                                <p>One of the greatest ideas of Emotion Engine is it's flexibility with libraries. You are encouraged to create your own package, containing only functionality your website needs. Full package is also slightly bigger.</p>
                            </li>
                        	<li>
                                <h6>Choose package</h6>
                                <p>Full package contains all libraries and all functionality Emotion Engine has to offer. System only contains only system files, library package contains all official library files, database package contains database classes and adapters.</p>
                            </li>
                            <li>
                                <h6>Get Started</h6>
                                <p>If this is your first time using Emotion Engine framework, it is recommended to go to the <a href="">learning pages</a> and read few guides.</p>
                            </li>
                            <li>
                                <h6>Get Support</h6>
                                <p>Anything gives you trouble and you cannot figure it out by reading guides and FAQ? Contact our support team and present your question.</p>
                            </li>    
                        </ul>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->
			
				<div id="sidebar">
					<div class="box_contact_r">
                        <div class="box_contact_t">
                            <div class="box_contact_b">
                                <div class="box_contact">
                                    <h5>Download Latest Version</h5>
                                <?php
									$collection = $this->Load ('Database')->Select ("collections");
									$collection = $collection[0];
									?>
                                    
                                    <img src="/application/img/archive-small.png" /> <a href="/download/release/full/">Emotion Engine Full Package (zip)</a><br />
                                    Version: <strong><?=$collection['version']?></strong><br />
                                    Revision: <strong><?=$collection['revision']?></strong>, Released: <?=date ('M j, Y', $collection['timestamp'])?><br />
                                    Size: <?php $fileSize = filesize ('release/EmotionEngine_' . $collection['version'] . '.zip'); echo ('<strong>' . $this->Load ('Text')->FormatFileSize ($fileSize) . '</strong> (' . number_format ($fileSize) . ' bytes)'); ?><br />
                                	MD5: <span style="font-size: 11px; font-weight: bold;"><?=md5_file ('release/EmotionEngine_' . $collection['version'] . '.zip')?></span><br />
                                    <br />
                                    <img src="/application/img/archive-small.png" /> <a href="/download/release/system/">Emotion Engine System Only (zip)</a><br />
                                    Version: <strong><?=$collection['version']?></strong><br />
                                    Revision: <strong><?=$collection['revision']?></strong>, Released: <?=date ('M j, Y', $collection['timestamp'])?><br />
                                    Size: <?php $fileSize = filesize ('release/EmotionEngine_' . $collection['version'] . '_system.zip');  echo ('<strong>' . $this->Load ('Text')->FormatFileSize ($fileSize) . '</strong> (' . number_format ($fileSize) . ' bytes)'); ?><br />
                               		MD5: <span style="font-size: 11px; font-weight: bold;"><?=md5_file ('release/EmotionEngine_' . $collection['version'] . '_system.zip')?></span><br />
                                    <br />
                                    <h5>Official Libraries</h5>
                                    <img src="/application/img/archive-small.png" /> <a href="/download/release/database/">Database package (zip)</a><br />
                                    Version: <strong><?=$collection['version']?></strong><br />
                                    Size: <?php $fileSize = filesize ('release/EmotionEngine_' . $collection['version'] . '_database.zip');  echo ('<strong>' . $this->Load ('Text')->FormatFileSize ($fileSize) . '</strong> (' . number_format ($fileSize) . ' bytes)'); ?><br />
                                    <br />
                                    <img src="/application/img/archive-small.png" /> <a href="/download/release/library/">Library package (zip)</a><br />
                                    Version: <strong><?=$collection['version']?></strong><br />
                                	Size: <?php $fileSize = filesize ('release/EmotionEngine_' . $collection['version'] . '_library.zip');  echo ('<strong>' . $this->Load ('Text')->FormatFileSize ($fileSize) . '</strong> (' . number_format ($fileSize) . ' bytes)'); ?><br />
                                    <br />
                                </div>
                            </div>
                        </div>
					</div><!-- end .box_contact_r -->
				</div><!-- end #sidebar -->

			</div><!-- end #main_content -->