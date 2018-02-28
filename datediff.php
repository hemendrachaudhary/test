<?php
$start  = date_create($wedding['wedding_date']);
						$end 	= date_create(); // Current time and date
						$diff  	= date_diff( $start, $end );

						echo 'The difference is ';
						echo  $diff->y . ' years, ';
						echo  $diff->m . ' months, ';
						echo  $diff->d . ' days, ';
						echo  $diff->h . ' hours, ';
						echo  $diff->i . ' minutes, ';
						echo  $diff->s . ' seconds';
            ?>
