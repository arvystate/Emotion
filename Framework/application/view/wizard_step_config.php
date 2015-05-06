			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>Configure</h1>
                        <h4>Your website needs some basic configuration.</h4>

                        <form method="post" name="create_form">
                        
                        <?php
						
						// To count checkboxes
						$count = 0;
						
						// Database configuration is shown if user is expert or if beginner choose db based website
						
						if ( ($data[0][0] == 1) || ( (is_numeric ($data[2][0]) === true) && (intval ($data[2][0]) > 0) ) )
						{
						?>
                        
                        <br />
                        <h3><strong>Database configuration</strong></h3>
                        <br />
                        Tick the boxes next to appropriate databases you are going to use in your web application.<br />
                        <br />
                        Database configuration variables must be entered manually due to security concearns.<br />
                        <br />
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tbody>
                        <tr>
                        <?php
						
						// Scan library files
						
						$files = @scandir ('database');
						$data[3][0] = explode ('-', $data[3][0]);
						
						// Loop through files and display
						foreach ($files as $value)
						{
							// Check if it is a valid file
							if ( ('.' . pathinfo ('database/' . $value, PATHINFO_EXTENSION) == EXTENSION) && (is_file ('database/' . $value) === true) )
							{
								// Open file and get information
								$fileData = file_get_contents ('database/' . $value);

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
								$packageLib = $fileData[2];
								
								// We do not want files from another package than library here
								if (strpos ($packageLib, 'database') === false)
								{
									continue;
								}
								
								$countDisplay = $count + 1;
								
								if ($countDisplay < 10)
								{
									$countDisplay = '0' . $countDisplay;
								}
								
								echo ('<td>');
								echo ('<label class="label_check" for="check-' . $countDisplay . '">');
								echo ('<input name="db_' . str_replace (EXTENSION, '', $value) . '" id="check-' . $countDisplay . '" value="1" type="checkbox" ');
								
								if (in_array (str_replace (EXTENSION, '', $value), $data[3][0]) === true)
								{
									echo ('checked="checked" ');
								}
								echo ('/> ');
								echo ('<h6>' . $title. '</h6></label></td>');
								
								if ( (($count + 1) % 4) == 0)
								{
									echo ('</tr>');
									echo ('<tr>');
								}
								
								
								$count++;
							}
						}
						
						?>
                        </tr>
                        </tbody>
                        </table>
                        <br />
                        <?php
						}
						?>

                        <br />
                        <h3><strong>Access points</strong></h3>
                        <br />
                        Access points are classes or files through which user can view your website.<br />
                        <br />
                        Enter names of access points you want created, each in it's own line.<br />
                        <br />
                        
                        <textarea rows="7" cols="75" name="access_points" class="text-input"><?php
						
						if (isset ($data[3][1]) === false)
						{
							if ($data[0][1] == 1)
							{
								echo ('index');
							}
							else
							{
								echo ('home');
							}
						}
						else
						{
							echo (implode ("\n", explode('-', $data[3][1])));
						}
						
						?></textarea><br />
                        
                        <br />
                        <h3><strong>Configuration variables</strong></h3>
                        <br />
                        
                        Those are global constants easily accessible throughout the application. They will be stored in Configuration class.<br />
                        <br />
                        Enter each variable in separate line. Double quotes are used for strings, colons and semicolons are not allowed.<br />
                        <br />
                        <textarea rows="7" cols="75" name="config" class="text-input"><?php
						
						if (isset ($data[3][2]) === false)
						{
							if ($data[0][0] == 0)
							{
								echo ('variable = "value"');
							}
							else
							{
								echo ("application_dir = \"application\"\nloader = \"config/loader\"\nautoload = true\ndefault_controller = \"home\"\nenable_query_strings = false\ncontroller_trigger = \"c\"\nfunction_trigger = \"m\"");
							}
						}
						else
						{
							echo ($data[3][2]);
						}
						
						?></textarea>
                        
                        <?php
						
						// Compiled config only availible in advanced mode
						if ($data[0][0] == 1)
						{
						?>
                        <label class="label_check" for="check-<?=++$count?>">
                        	<input name="separate_config" id="check-<?=$count?>" value="1" type="checkbox" <?php if ( (intval ($data[3][3]) == 1) || (isset ($data[3][3]) === false) ) { echo ('checked="checked"'); } ?> /> <h6>Use separate classes for configuration and loader</h6>
                        </label>
                        <?php
						}
						?>
                        <br />
                        <br />
                        
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()"><?php if ($step > 3) { echo ('Save'); }else{ echo ('Next'); } ?></a></h3>
                        </div>
                        
                        <input type="hidden" name="step_info" value="3" />
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->