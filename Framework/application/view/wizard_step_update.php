			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>Update package</h1>
                        <h4>Easy way to update, just paste your wizard configuration data into the box below and you are already configured!</h4>
                        
                        <form method="post" name="create_form">
                        
                        <br />
                        <textarea rows="7" cols="75" name="update_data" class="text-input"><?php
						
						if (is_array ($data) === true)
						{
							$data = '';
						}
						
						echo ($data);
						?></textarea>
                      	
                        <br />
                        <br />
                        <?php
						if ($msg['update_error'])
						{
							echo ('<span class="error">Unable to read configuration data, please try again.</span><br /><br />');
						}
						?>
                        
                        <div style="text-align: right;">
							<h3><a href="javascript:document.create_form.submit()">Proceed</a></h3>
                        </div>
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->