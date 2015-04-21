<?php
/*************************************************************************************
 * ee.php
 * ----------
 * Author: David Dexter
 * Copyright: (c) 2011 David Dexter
 * Release Version: 1.0.8.10
 * Date Started: 2011/10/11
 *
 * ExpressionEngine template language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2011/10/11 (1.0.0)
 *  -  Initial Release
 *
 * TODO
 * ----
 *
 *************************************************************************************
 *
 * 	This file is part of GeSHi.
 *
 *  GeSHi is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  GeSHi is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with GeSHi; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'ExpressionEngine',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array('{!--' => '.*?--}'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array(
            'if', 'elseif', 'else'
            ),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array(),
        7 => array(),
        8 => array()
        ),
    'SYMBOLS' => array(
        '/', '=', '==', '!=', '>', '<', '>=', '<=', '!', '%'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        6 => false,
        7 => false,
        8 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #990000;',        	//Functions
            2 => 'color: #008000;',        	//Modifiers
            3 => 'color: #0600FF;',        	//Custom Functions
            4 => 'color: #804040;',        	//Variables
            5 => 'color: #008000;',        	//Methods
            6 => 'color: #6A0A0A;',        	//Attributes
            7 => 'color: #D36900;',        	//Text-based symbols
            8 => 'color: #0600FF;'        	//php functions
            ),
        'COMMENTS' => array(
            'MULTI' => 'color: #AAA; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #D36900;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            1 => 'color: #006600;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #D36900;'
            ),
        'SCRIPT' => array(
            0 => '',
            1 => 'color: #808080; font-style: italic;',
            2 => 'color: #009000;'
            ),
        'REGEXPS' => array(
				0 	=> 'color: #0600FF',
				1	=> 'color: #FF3300;',
				2 	=> 'color: #00aaff;'
        	)
        ),
    'URLS' => array(
				        1 => '',#'http://www.expresssionengine.com/{FNAMEL}',
				        2 => '',
				        3 => '',
				        4 => '',
				        5 => '',
				        6 => '',
				        7 => '',
				        8 => ''
        			),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
        ),
    'REGEXPS' => array(
        // variables
	        0 => '(exp:([\\w]+):([\\w]+)(?:\\s|(?:\\s(?:[^\'\\"}\\s]|\'[^\']*\'|\\"[^\\"]*\\")*))*)',
	        1 => '({)(\/)?([a-z0-9_-]+)(})', 
	        2 => '([a-z0-9_:-]+)(\s*)(=)'
        ),
    'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
    'SCRIPT_DELIMITERS' => array(
        0 => array(
            '{' => '}'
            )
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => true,
        1 => true,
        2 => true
    ),
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            'DISALLOWED_BEFORE' => "(?<![a-zA-Z0-9\$_\|\#;>|^])",
            'DISALLOWED_AFTER' => "(?![a-zA-Z0-9_<\|%\\-&])"
        )
    )
);