			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>General Information</h1>
                        <h4>First we need to know what kind of user you are, so we can provide you the best creation experience possible.</h4>
                        <br />
                        <form method="post" name="create_form">
                        
                        <label class="label_radio" for="radio-01">
                        	<input name="user_mode" id="radio-01" value="0" type="radio" <?php if ($data[0][0] == 0){ echo ('checked="checked"'); } ?> /> <h6>Beginner</h6><br />
                            This is your first time using Emotion Engine Framework. You are not familiar with it's concepts, libraries, but you understand basics of web development, 
                            such as HTML, CSS, JavaScript and PHP.
                        </label><br />
                        <br />
                        <label class="label_radio" for="radio-02">
                        	<input name="user_mode" id="radio-02" value="1" type="radio" <?php if ($data[0][0] == 1){ echo ('checked="checked"'); } ?> /> <h6>Expert</h6><br />
                            You have used Emotion Engine Framework before. You know which classes you need, what concepts you will use and similar. Use this mode, to get the most.
                        </label><br />
                        <br />
                        <label class="label_radio" for="radio-03">
                        	<input name="user_mode" id="radio-03" value="2" type="radio" <?php if ($data[0][0] == 2){ echo ('checked="checked"'); } ?> /> <h6>Update</h6><br />
                            You have Emotion Engine configuration data saved and you want to reconfigure and update your current configuration without going through whole process again.
                        </label>
                        <br />
                        <br />
                        <h4>Choose architecture you wish to use with your new application.</h4>
                        <br />
                        
                        <label class="label_radio" for="radio-04">
                        	<input name="architecture" id="radio-04" value="0" type="radio" <?php if ($data[0][1] == 0){ echo ('checked="checked"'); } ?> /> <h6>Model - View - Controller</h6><br />
                            Many modern programmers use Model - View - Controller software architecture. It's main concept is dividing code into human understandable sections.
                        </label><br />
                        <br />
                        <label class="label_radio" for="radio-05">
                        	<input name="architecture" id="radio-05" value="1" type="radio" <?php if ($data[0][1] == 1){ echo ('checked="checked"'); } ?>/> <h6>Procedural</h6><br />
                            A little bit older, also common way to write code is to use each page as it's own file. However, routing features are not availible in this option.
                        </label>
                        <br />
                        <br />
                        <h4>Enter valid E-mail address if you wish to receive package configuration data once you have finished.</h4>
                        
                        <span style="margin-left: 20px;"><input name="email" id="email" size="30" value="<?php echo ($data[0][2]); ?>" class="text-input" type="text" /></span> 
                        
                        <?php
						if ($msg['email_error'])
						{
							echo ('<span class="error">E-Mail address does not appear to be valid.</span>');
						}
						?>
                        
                        <br />
						<br />
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()"><?php if ($step > 0) { echo ('Save'); }else{ echo ('Next'); } ?></a></h3>
                        </div>
                        
                        <input type="hidden" name="step_info" value="0" />
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->