			<div id="main_content">
                <div id="content">
                  <div class="front_page">
        
                        <h1>Download</h1>
                        <h4>Congratulations, your package is now complete. It will be ready in a minute.<br />
                        <br />
                  		Thank you for using our service.</h4>
                        <br />
                      	Changing package information is no longer possible. Your package will be availible for <strong>1 hour</strong>, 
                        then you will have to wait <strong>24 hours</strong> until you can create another package. 
                        Please understand those conditions, they are placed only to reduce server load.<br />
                    	<br />
                        <br />
                        
                        <div id="download_link"></div>
                        
                        <div id="loader"><img src="/application/img/loader.gif" /></div>
                        
                        <script type="text/javascript">
						
						function createDownload ()
						{
							$("#download_link").load("/download/package/", function ()
							{
								var linkReady = document.getElementById("download_link").innerHTML;
								
								if (linkReady.indexOf("generate") == -1)
								{
									document.getElementById("loader").innerHTML = "";
									clearInterval (timer);
								}
							}
							);
						}
						
						createDownload ();
						
						var timer = setInterval ("createDownload()", 10000);
						</script>
                        <br />
                        <h5>Configuration code</h5>
                        <div style="font-family:'Courier New', Courier, monospace;">
                        <?php 
						
						// Create data string
						$dataString = '';
						
						foreach ($data as $steps)
						{
							$dataString .= implode (':', $steps) . ';';
						}
						
						$dataString = substr ($dataString, 0, -1);
						
						$rsa = new Crypt_RSA ();
						$rsa->setEncryptionMode (CRYPT_RSA_ENCRYPTION_PKCS1);
						
						// Encrypt using public key
						$rsa->loadKey ('MIGJAoGBAIul2S+XQ06TztTWoQj2g+BSIFN0UnHJSQ1xIVPAdHH3VB7CiNgdMtWOSprD+Jt7qRusifvuVxypNnuO85c0lD96B5aq99ACV5rBDoZPMuqNS2Awn7cOQvy4NbfHiNg+umYqwWGuv/Yf6kRqD9BS167yyo9i4yp+3IBRuQYOToLhAgMBAAE=');
		
						
						echo ($this->Load ('Text')->InsertString (base64_encode ($rsa->encrypt ($dataString)), 80) );
						?>
                        </div>
                        <br />
                        Save this information, if you ever want to update your system with new version. It was also sent to your E-mail address, if you had entered it.<br />
                        <br />
                  </div><!-- end .front_page -->
                </div><!-- end #content -->