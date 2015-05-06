			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>Finishing Options</h1>
                        <h4>Before your package is completed, you still need to select some options.</h4>
                        
                        <form method="post" name="create_form">
                        <br />
                        <h3><strong>Basic</strong></h3>
                        <br />
                        <?php
						
						if ($data[0][0] == 1)
						{
						?>
                        <table cellpadding="4" cellspacing="4" border="0">
                        <tbody>
                        <tr>
                        	<td><h6>File extension:</h6></td>
                            <td rowspan="2" style="padding-left: 15px;" valign="bottom"><input name="opt_extension" type="text" size="5" class="text-input" value="<?php echo ($data[4][9]); ?>" maxlength="5" /></td>
                        </tr>
                        <tr>
                        	<td>All created files will have desired extension.<br />Extension is maximum 4 character long.</td>
                        </tr>
                        </tbody>
                        </table>
                        <br />
                        <?php
						}
						?>
                        <label class="label_check" for="check-01">
                        	<input name="opt_debug" id="check-01" value="1" type="checkbox" <?php if (intval ($data[4][0]) == 1) { echo ('checked="checked"'); } ?>/> <h6>Debugging</h6><br />
							Adds debug class and functions, to fully control your development.
                        </label><br />
                        <br />
                        
                        <?php
						
						if ($data[0][1] == 0)
						{
						?>
                        
                        <label class="label_check" for="check-02">
                        	<input name="opt_htaccess" id="check-02" value="1" type="checkbox" <?php if (intval ($data[4][1]) == 1) { echo ('checked="checked"'); } ?>/> <h6>Rewrite Conditions</h6><br />
							Adds .htaccess file with Apache Rewrite Module conditions to be used with MVC architecture.
                        </label><br />
                        <br />
                        
                        <?php
						}
						
						if ($data[0][0] == 1)
						{
						?>
                        <label class="label_check" for="check-03">
                        	<input name="sample_dirs" id="check-03" value="1" type="checkbox" <?php if ( (intval ($data[4][2]) == 1) || ($data[4][2] == '') ) { echo ('checked="checked"'); } ?> /> <h6>Directories</h6><br />
                            Creates default directory structure for configuration, loaders and libraries.
                        </label><br />
                        
                        <?php
						}
						?>
                        
                        <br />
                        <h3><strong>Samples</strong></h3>
                        <br />
                        Choose which sample code files you would like added to your package.<br />
                        <br />
                        
                        <label class="label_check" for="check-04">
                        	<input name="sample_loaders" id="check-04" value="1" type="checkbox" <?php if ( (intval ($data[4][3]) == 1) || (isset ($data[4][3]) === false) ) { echo ('checked="checked"'); } ?> /> <h6>Loaders</h6>
                        </label><br />
                        
                        <label class="label_check" for="check-06">
                        	<input name="sample_views" id="check-06" value="1" type="checkbox" <?php if (intval ($data[4][5]) == 1) { echo ('checked="checked"'); } ?> /> <h6>Views</h6>
                        </label><br />
                        
                        <?php
						
						// MVC
						if ($data[0][1] == 0)
						{
						?>

                        <label class="label_check" for="check-05">
                        	<input name="sample_controllers" id="check-05" value="1" type="checkbox" <?php if (intval ($data[4][4]) == 1) { echo ('checked="checked"'); } ?> /> <h6>Controllers</h6>
                        </label><br />
                        
                        <?php
						}
						?>
                        
                        <br />
                        <h3><strong>Optimizations</strong></h3>
                        <br />
                        <label class="label_check" for="check-07">
                        	<input name="opt_comment" id="check-07" value="1" type="checkbox" <?php if (intval ($data[4][6]) == 1) { echo ('checked="checked"'); } ?>/> <h6>Remove comments</h6><br />
							Removes all comments used by Emotion Engine developers, since none are required when system is in production. Both inline and multiline comments are removed.
                        </label><br />
                        <br />
                        <label class="label_check" for="check-08">
                        	<input name="opt_shrink" id="check-08" value="1" type="checkbox" <?php if (intval ($data[4][7]) == 1) { echo ('checked="checked"'); } ?>/> <h6>Shrink code</h6><br />
							Reformats and shrinks code to use minimal spaces, tabs and new lines. Code is harder to read, but files become smaller and consume less memory.
                        </label><br />
                        <br />
                        <label class="label_check" for="check-09">
                        	<input name="opt_compile" id="check-09" value="1" type="checkbox" <?php if (intval ($data[4][8]) == 1) { echo ('checked="checked"'); } ?>/> <h6>Compile code</h6><br />
							To achieve the fastest loading times, whole system can be compiled into one file. That way, there the system is not using many slow functions such as include.
                        </label><br />
                        <br />
                        
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()"><?php if ($step > 4) { echo ('Save'); }else{ echo ('Next'); } ?></a></h3>
                        </div>
                        
                        <input type="hidden" name="step_info" value="4" />
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->