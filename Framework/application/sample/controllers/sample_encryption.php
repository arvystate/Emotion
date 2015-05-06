<?php
/**
 * Sample_Encryption
 *
 * Short sample controller to display Encryption library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Encryption extends EmotionController
{
	public function index ()
	{
		//
		// Text we are going to encrypt using symmetric AES encryption
		//
		
		echo ('Plain text:<br /><br />');
		
		$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ligula turpis, accumsan non convallis nec, semper id neque. ';
		$text .= 'Aenean at risus vel arcu tempor luctus et quis metus. Praesent fermentum lectus in arcu sagittis commodo commodo odio mollis. ';
		$text .= 'Suspendisse potenti. Vestibulum fringilla aliquet facilisis. Sed quam risus, iaculis at placerat vel, pretium ut eros. ';
		$text .= 'Sed ante arcu, tincidunt quis placerat quis, lacinia id ante. Ut pretium viverra odio at lacinia. Morbi porttitor mi a odio tristique sodales. ';
		$text .= 'Donec urna erat, egestas et fermentum sed, consectetur ac ligula. Sed ultricies sem in arcu ullamcorper sit amet lobortis mi rhoncus.';
		
		echo ($text . '<br /><br />');
		
		//
		// To part long strings we use Text library and we insert a break every 100 characters
		//
		
		//
		// Encrypt text using TripleDES
		//
		
		$encryptedDES = $this->Load ('Encryption')->TripleDesEncrypt ($text, '123456781234567812345678');
		
		echo ('TripleDES:<br /><br />' . $this->Load ('Text')->InsertString ($encryptedDES, 100) . '<br /><br />');
		
		//
		// Encrypt text using Advanced Encryption Standard
		//
		
		$encryptedAES = $this->Load ('Encryption')->AesEncrypt ($text, '12345678123456781234567812345678');
		
		echo ('AES-256:<br /><br />' . $this->Load ('Text')->InsertString ($encryptedAES, 100) . '<br /><br />');
	}
}

?>