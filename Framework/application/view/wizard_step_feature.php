			<div id="main_content">
                <div id="content">
                    <div class="front_page">
                    	<h1>Features</h1>
                        
                        <form method="post" name="create_form" id="create_form">
                    	<?php
						
						// Advanced
						if ($data[0][0] == 1)
						{
						?>
                         <!-- ADVANCED VIEW -->
                        
                        <h4>Emotion Engine has many features. Tick boxes next to those you will use in your application.</h4>
                        
                        <br />
                        
                        <?php
						
						// Scan library files
						
						$files = @scandir ('library');
						
						$count = 1;
						
						// Loop through files and display
						foreach ($files as $value)
						{
							// Check if it is a valid file
							if ( ('.' . pathinfo ('library/' . $value, PATHINFO_EXTENSION) == EXTENSION) && (is_file ('library/' . $value) === true) )
							{
								// Open file and get information
								$fileData = file_get_contents ('library/' . $value);

								// Cut away to first comment
								$fileData = substr ($fileData, strpos ($fileData, '}'));

								// Cut away to class start
								$fileData = substr ($fileData, 0, strpos ($fileData, '{'));
								
								$fileData = substr ($fileData, strpos ($fileData, '/**') + 4);
								$fileData = substr ($fileData, 0, strpos ($fileData, '**/') - 2);
								
								$fileData = explode ("\n", $fileData);
								$fileData = str_replace (' * ', '', $fileData);
								$fileData = str_replace (' *', '', $fileData);
								
								foreach ($fileData as $key => $value2)
								{
									$fileData[$key] = trim ($value2);
									
									if (empty ($fileData[$key]) === true)
									{
										unset ($fileData[$key]);
									}
								}
								
								$fileData = array_values ($fileData);
								
								$title = $fileData[0];
								$description = $fileData[1];
								$packageLib = $fileData[2];
								$require = $fileData[3];
								
								if ($title == 'Debug')
								{
									continue;
								}
								
								// We do not want files from another package than library here
								if (strpos ($packageLib, 'library') === false)
								{
									continue;
								}
								// Also we do not allow required files to be de-selected
								else if (stripos ($require, 'true') !== false)
								{
									continue;
								}
								
								$countDisplay = $count;
								
								if ($countDisplay < 10)
								{
									$countDisplay = '0' . $countDisplay;
								}
								
								$count++;
								
								echo ('<label class="label_check" for="check-' . $countDisplay . '">');
								echo ('<input name="lib_' . str_replace (EXTENSION, '', $value) . '" id="check-' . $countDisplay . '" value="1" type="checkbox" ');
								
								if (in_array (str_replace (EXTENSION, '', $value), $data[2]) === true)
								{
									echo ('checked="checked" ');
								}
								echo ('/> ');
								echo ('<h6>' . $title. '</h6><br />' . $description . '</label><br /><br />');
							}
						}
						
						?>
                        <div style="border-top: #585858 1px solid; display: block; margin-top: 15px; margin-bottom: 15px;"></div>
                        
                        <label class="label_check" for="check-14">
                        	<input name="select_all" id="check-14" value="1" type="checkbox" onclick="checkUncheckAll ();" /> <h6>Select All</h6><br />
                        </label><br />
                        
                        <script type="text/javascript">
                        function checkUncheckAll ()
						{
							var theForm = document.forms['create_form'];
							var selectAll = document.getElementById ('check-14');
							
							for (var i = 0; i < theForm.length; i++)
							{							
								if ( (theForm[i].type == 'checkbox') && (theForm[i].name != 'select_all') )
								{
									theForm[i].checked = selectAll.checked;
								}
							}
						}
						
						function checkSelectAll ()
						{
							var theForm = document.forms['create_form'];
							var selectAll = document.getElementById ('check-14');
							
							var count = 0;
							var checkBoxCount = 0;
							
							for (var i = 0; i < theForm.length; i++)
							{							
								if ( (theForm[i].type == 'checkbox') && (theForm[i].name != 'select_all') && (theForm[i].checked == true) )
								{
									count++;
								}
								
								if ( (theForm[i].type == 'checkbox') && (theForm[i].name != 'select_all') )
								{
									checkBoxCount++;
								}
							}
							
							if (count == checkBoxCount)
							{
								selectAll.checked = true;
							}
						}
						
						checkSelectAll ();
	  					</script>
                        <?
						}
						// Beginner
						else
						{
						?>
                        <h4>Choose the type of web application you are building..</h4>
                        
                        <label class="label_radio" for="radio-01">
                        	<input name="website" id="radio-01" value="0" type="radio"<?php if ( ($data[2][0] == 0) && (is_numeric ($data[2][0]) === true) ){ echo ('checked="checked"'); } ?> /> <h6>Introduction</h6><br />
                            Introduction website is a basic website for smaller companies or personal website. It contains basic functionality for multi-language websites, text operations,
                            and image functions. It does support dynamic content, but it has no database support.
                        </label>
                        <br />
                        <br />
                        <!--Includes classes: <strong>image, language, text</strong><br />
                        <br />-->
                        <label class="label_radio" for="radio-02">
                        	<input name="website" id="radio-02" value="1" type="radio" <?php if ( ($data[2][0] == 1) && (is_numeric ($data[2][0]) === true) ){ echo ('checked="checked"'); } ?>/> <h6>Advanced Introduction</h6><br />
                            Advanced introduction website is extended basic introduction site and it supports database management system. It is aimed at small to medium companies and bigger 
                            personal websites.
                        </label>
                        <br />
                        <br />
                        <!--Includes classes: <strong>database, image, language, text</strong><br />
                        <br />-->
                        <label class="label_radio" for="radio-03">
                        	<input name="website" id="radio-03" value="2" type="radio" <?php if ( ($data[2][0] == 2) || (is_numeric ($data[2][0]) === false) ){ echo ('checked="checked"'); } ?> /> <h6>Dynamic Content</h6><br />
                            Dynamic content websites are basically the most common used web applications. This site contains all functionality to build a basic content management system 
                            or blogs. It supports user authentication and file uploading.
                        </label>
                        <br />
                        <br />
                        <!--Includes classes: <strong>database, image, language, session, upload, text</strong><br />
                        <br />-->
                        <label class="label_radio" for="radio-04">
                        	<input name="website" id="radio-04" value="3" type="radio" <?php if ( ($data[2][0] == 3) && (is_numeric ($data[2][0]) === true) ){ echo ('checked="checked"'); } ?> /> <h6>Advanced Dynamic Content</h6><br />
                            Advanced dynamic content website are enhanced dynamic content sites. If you are looking to create a forum platform or complex content management system, 
                            choose this site.
                        </label>
                        <br />
                        <br />
                        <!--Includes classes: <strong>captcha, database, image, language, session, template, upload, text</strong><br />
                        <br />-->
                        <label class="label_radio" for="radio-05">
                        	<input name="website" id="radio-05" value="4" type="radio" <?php if ( ($data[2][0] == 4) && (is_numeric ($data[2][0]) === true) ){ echo ('checked="checked"'); } ?> /> <h6>Enterprise</h6><br />
                            Enterprise websites are complex websites designed for professional (large) businesses. They contain almost all possible functions and their developers 
                            attempt to reach the best.
                        </label>
                        <br />
                        <br />
                        <!--Includes classes: <strong>banlist, captcha, database, encryption, ftp, image, language, session, template, text, upload, zip</strong><br />
                        <br />-->
                        <br />
                        <?php
						}
						
						?>               
                        
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()"><?php if ($step > 2) { echo ('Save'); }else{ echo ('Next'); } ?></a></h3>
                        </div>
                        
                        <input type="hidden" name="step_info" value="2" />
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->