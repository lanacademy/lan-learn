*************************
* Plugins Documentation *
*************************
Editors Note:
If something cannot be found here and exists, it probably resides within the theme.

=========================

./at_navigation/at_helper.php

Sets certain variables for use in at_navigation.php. This plugin mostly deals with the multi-course system, and allows the name of the site to adapt to the current course by reading the URL.


./at_navigation/at_navigation.php

This plugin generates the sidebar navigation from courses within the /content/ folder, and the resulting HTML output is stored in the 'at_navigation' twig variable. Do note that the HTML is generated here, and not within the theme. Themes only have control over where to place the entire navigation.

=========================

./helper/quizlog.php 

This file gets called by quizzes via AJAX (reference the quiz JS files within the theme) to create a score entry within a user's log file.

=========================

./pico_private/users

All users' XML data is stored here.


./pico_private/pico_private.php

Provides back-end for the login system. Utilizes sessions, and can be called by the front-end via sitename.com/login/.


./pico_private/pico_register.php

Provides back-end for the registration system. Form details can be seen by analyzing the 'before_render' hook. Can be called by the front-end via sitename.com/register/.

=========================

./pknote_service/note_storage.php

WIP! This is the backend for the notes system, where it reads and writes to a user's personal notes file. It currently uses a default file and does not call upon any of the session variables for user authentication.

=========================

./pkwiwi_service/wikilog.php

This file is called via AJAX (reference pk_wikilog JS files within the theme) to create a log entry for wiki hits created by user, when a user is logged in.

=========================

./adv_meta.php

Allows for the system to define and create new meta types, added into the config, specified within the content.

=========================

./pico_commonthemeheader.php

Re-implements common theme headers in twig for pico.

=========================

./pico_dashboard.php

Creates the HTML for the 'dashboard' twig variable. Parses log files and then does some math to determine timestamp differences for time stats and counts occurences for test/quiz stats.  Also parses out the last content page that a user had visited and displays it as a bookmark, as well as the last 10 keyword links (wikipedia) they have clicked.
(editors note: Might still be doing file reads on the log file, can be optimized to only do one)

=========================

./pico_flowplayer.php

Allows '!!' to be used within Markdown to specify a video file to be loaded and played upon user request.

=========================

./pico_metakeywords.php

Populates 'my_keywords' twig variable to contain all the keywords found on the current page from 'keywords.xml' in the content. This is used for the sidebar quizzes (see quizme JS + pico_pagequiz).

=========================

./pico_pagequiz.php

Generates the data used for the JS sidebar quiz. Reference jquizme JS files. Pulls questions and answers from 'keywords.xml'.

=========================

./pico_pagequiz.php

Generates a dynamic quiz for the sidebar by reading keywords 

=========================

./pico_plugin.php

Template file containing all the blank hooks provided by pico

=========================

./pico_quiz.php

(editors note: this plugin is mislabeled, it actually generates chapter "tests", quizzes are made for the sidebar by ./pico_pagequiz.php)
Reads markdown files labeled with the 'quiz' meta tag, and parses them to generate an appropriate test and response form.  Responses are graded and stored to the log (Warning: it does not currently limit the number of times users can take a test).

=========================

./pico_returnpage.php

Adds twig variable 'current_url'
(editors note: This might/could be extraneous, we didn't make it)

=========================

./pico_tracking.php

This plugin stores data about the page hits for logged in users, writing to their respective log files.  See /log/some_username.log for an example of the output.