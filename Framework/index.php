<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @core                                          *
 *   File                 : index.php                                      *
 *   Version              : 1.0.0                                          *
 *   Status               : Compiled                                       *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Apr 4, 2011                          *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Apr 4, 2011                          *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   User access point for MVC applications.                               *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/04/11] - File created                                          *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *                                                                         *
 *   Emotion is a powerful PHP framework for website generation.           *
 *   -------------------------------------------------------------------   *
 *   Application is owned and copyrighted by ArvYStaTe.net Team, you are   *
 *   only allowed to modify code, not take ownership or in any way claim   *
 *   you are the creator of any thing else but modifications.              *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************/

 //
 // Below is the complete list of implemented procedures -> samples example
 //

 //
 // For all emotion pages
 //

define ('EMOTION_PAGE', true); // If this variable is set to true
require_once ('system/emotion.php'); // Load the Emotion class

//
// Constructors
//

// Default constructor loads configuration from application/config/config.php file and loader if it is defined in config
$system = new EmotionEngine ();
// Loads configuration from custom file
//$system = new EmotionEngine ('application/config/config.php');
// No configuration load -> system may be used for supporting another system or such
//$system = new EmotionEngine (false);

// This is everything that needs to be in index.php, if you will use index.php as application entry point for MVC pattern
$system->UseFrontController ();

?>