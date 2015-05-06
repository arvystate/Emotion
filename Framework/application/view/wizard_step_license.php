			<div id="main_content">
                <div id="content">
                    <div class="front_page">
        
                        <h1>License Agreement</h1>
                        <h4>Before you proceed please read license agreement.<br /><br />You must accept the terms of license agreement, before proceeding.</h4>
                        
                        <br />
                        <h3><strong>BSD LICENSE CONDITIONS AND TERMS</strong></h3>
                        <br />

						<span style="font-style: oblique; font-family:'Lucida Sans Unicode', 'Lucida Grande', sans-serif;">
                        Copyright (c) 2003-2011, ArvYStaTe.net Team<br />
                        All rights reserved.<br />
                        <br />
                        <br />                        
                        Redistribution and use in source and binary forms, with or without modification,
                        are permitted provided that the following conditions are met:<br />
                        <br />
                            * Redistributions of source code must retain the above copyright notice,
                              this list of conditions and the following disclaimer.<br />
                        <br />
                            * Redistributions in binary form must reproduce the above copyright notice,
                              this list of conditions and the following disclaimer in the documentation
                              and/or other materials provided with the distribution.<br />
                        <br />
                            * Neither the name of ArvYStaTe.net nor the names of its
                              contributors may be used to endorse or promote products derived from this
                              software without specific prior written permission.<br />
                        <br />
                        THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
                        ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
                        WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
                        DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
                        ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
                        (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
                        LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
                        ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
                        (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
                        SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.<br />
                        </span>
                        
                        <br />
						<br />
                        
                        <form method="post" name="create_form">
                        
                        <?php
						if ($msg['license_error'])
						{
							echo ('<span class="error">You must accept license agreement to continue.</span><br /><br />');
						}
						?>
                        
                        <label class="label_check" for="check-01">
                        	<input name="license" id="check-01" value="1" type="checkbox" <?php if ($step > 1) { echo ('disabled="disabled" '); } if ($data[1][0] == 1){ echo ('checked="checked"'); } ?>/> <h6>I accept the terms in the license agreement</h6> 
                        </label>
                        
                        <?php
                        if ($step == 1)
						{
							echo ('<div style="text-align: right;"><h3><a href="javascript:document.create_form.submit()">Next</a></h3></div>');
						}
						?>
                        
                        <input type="hidden" name="step_info" value="1" />
                        
                        </form>
                    </div><!-- end .front_page -->
                </div><!-- end #content -->