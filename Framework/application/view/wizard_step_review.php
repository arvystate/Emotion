			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        				
                        <h1>Review</h1>
                        <h4>Before we create your package, review your configuration, this is the last time before you can change it.<br /><br />Remember, you will have to wait <strong>24 hours</strong> until you can create next package.</h4>
                        
                        <br />
                        <table cellspacing="4" cellpadding="4" border="0" width="80%">
                        <tbody>
                        <tr>
                        	<td colspan="2"><h3><strong>1. General</strong></h3></td>
                        </tr>
                        <tr>
                        	<td>Architecture:</td>
                            <td><strong><?php if ($data[0][1] == 0){ echo ('Model - View - Controller'); } else { echo ('Procedural'); } ?></strong></td>
                        </tr>
                        <?php
						
						if (empty ($data[0][2]) === false)
						{
						?>
                        <tr>
                        	<td>Email:</td>
                            <td><strong><?=$data[0][2]?></strong></td>
                        </tr>
                        <?php
						}
						?>
                        <tr>
                        	<td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                        	<td colspan="2"><h3><strong>2. Features</strong></h3></td>
                        </tr>

						<?php
                        
                        if ($data[0][0] == 1)
                        {
							echo ('<tr><td valign="top">Features:</td><td>');
                            echo ('<strong>');
                            
                            foreach ($data[2] as $value)
                            {
                                if (is_numeric ($value) === false)
                                {
                                    echo (ucfirst ($value) . '<br />');
                                }
                            }
                            
                            echo ('</strong>');
							echo ('</td></tr>');
                        }
                        else
                        {
							echo ('<tr><td>Website type:</td><td>');
                            echo ('<strong>');
							
                            switch ($data[2][0])
                            {
                                case 1:
                                    echo ('Advanced Introduction');
									
                                    break;
                                case 2:
									echo ('Dynamic Content');
								
                                    break;
                                case 3:
									echo ('Advanced Dynamic Content');
								
                                    break;
                                case 4:
									echo ('Enterprise');
								
                                    break;
								default:
									echo ('Introduction');
								
									break;
                            }
							
							echo ('</strong>');
							echo ('</td></tr>');
							
							echo ('<tr><td valign="top">Features:</td><td>');
                            echo ('<strong>');
							
							$features[0] = array ('image', 'language', 'text');
							$features[1] = array ('database', 'image', 'language', 'text');
							$features[2] = array ('database', 'image', 'language', 'session', 'upload', 'text');
							$features[3] = array ('captcha', 'database', 'image', 'language', 'session', 'template', 'upload', 'text');
							$features[4] = array ('banlist', 'captcha', 'database', 'encryption', 'image', 'language', 'session', 'template', 'text', 'upload', 'zip');
                            
                            foreach ($features[$data[2][0]] as $value)
                            {
                            	echo (ucfirst ($value) . '<br />');
                            }
                            
                            echo ('</strong>');
							echo ('</td></tr>');
                        }							
                        ?>
                        <tr>
                        	<td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                        	<td colspan="2"><h3><strong>3. Configuration</strong></h3></td>
                        </tr>
                        <tr>
                        	<td valign="top">Database:</td>
                            <td>
                            <strong>
                            <?php
							
							if ( (empty ($data[3][0]) === true) || ( (is_numeric ($data[2][0]) === true) && ($data[2][0] == 0) && ($data[0][0] == 0) ) )
							{
								echo ('None');
							}
							else
							{
								$supportedDatabases = array ('Firebird', 'MSSQL', 'MySQL', 'MySQLi', 'ODBC', 'Oracle', 'PostgreSQL', 'SQLite');
								$databaseId = array ('firebird', 'mssql', 'mysql', 'mysqli', 'odbc', 'oracle', 'postgresql', 'sqlite');
								
								$used = explode ('-', $data[3][0]);
								
								$count = 0;
								
								foreach ($used as $value)
								{
									$search = array_search ($value, $databaseId);
									
									if ($search !== false)
									{
										echo ($supportedDatabases[$search] . '<br />');
										$count++;
									}
								}
								
								if ($count == 0)
								{
									echo ('None');
								}
							}
							
							?>
                            </strong>
                            
                            </td>
                        </tr>
                        <tr>
                        	<tr>
                        	<td valign="top">Access points:</td>
                        	<td>
                            <strong><?=$this->Load ('Text')->Nl2brExceptPre ($data[3][1])?></strong>
                            </td>
                        </tr>
                        <?php
						
						if ($data[0][0] == 1)
						{
						?>
                        <tr>
                        	<td>Separate configuration:</td>
                            <td><?php if ($data[3][3] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <?php
						}
						?>
                        <tr>
                        	<td valign="top">Variables:</td>
                        	<td>
                            <strong>
							<?php
                            $variables = $this->Load ('Text')->Nl2brExceptPre ($data[3][2]);
							
							if ($variables == '')
							{
								echo ('None');
							}
							else
							{
								echo ($variables);
							}
							?>
                            </strong>
                            </td>
                        </tr>
                        <tr>
                        	<td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                        	<td colspan="2"><h3><strong>4. Options</strong></h3></td>
                        </tr>
                        <?php
                        
						if ($data[0][0] == 1)
						{
						?>
                        <tr>
                        	<td>Extension:</td>
                            <td><strong><?='.' . $data[4][9]?></strong></td>
                        </tr>
                        <?php
						}
						?>
                        <tr>
                        	<td>Debugging mode:</td>
                            <td><?php if ($data[4][0] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <?php
						if ($data[0][1] == 0)
						{
						?>
                        <tr>
                        	<td>Rewrite Conditions:</td>
                            <td><?php if ($data[4][1] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <?php
						}
						?>
						
                        <?php
						if ($data[0][0] == 1)
                        {
                        ?>
                        <tr>
                        	<td>Directories:</td>
                            <td><?php if ($data[4][2] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <?php
						}
						?>
                        <tr>
                        	<td>Sample loaders:</td>
                            <td><?php if ($data[4][3] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <tr>
                        	<td>Sample views:</td>
                            <td><?php if ($data[4][5] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <?php						
						if ($data[0][1] == 0)
						{
						?>
                        <tr>
                        	<td>Sample controllers:</td>
                            <td><?php if ($data[4][4] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                     	<?php
						}
						?>
                        <tr>
                        	<td>Remove comments:</td>
                            <td><?php if ($data[4][6] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <tr>
                        	<td>Shrink code:</td>
                            <td><?php if ($data[4][7] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        <tr>
                        	<td>Compile code:</td>
                            <td><?php if ($data[4][8] == 1) { echo ('<img src="/application/img/yes.png" />'); } else { echo ('<img src="/application/img/no.png" />'); } ?></td>
                        </tr>
                        </tbody>
                        </table>
                        <br />
						<h4>Once you have verified this information, continue and click download.</h4><br />
                        <br />
                        
                        <form method="post" name="create_form">
                        
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()">Download</a></h3>
                        </div>
                        
                        <input type="hidden" name="step_info" value="5" />
                        
                        </form>
                        
                    </div><!-- end .front_page -->
                </div><!-- end #content -->