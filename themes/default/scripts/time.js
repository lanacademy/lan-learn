// This function gets the current time and injects it into the DOM
           function updateClock() {
               // Gets the current time
               var now = new Date();

               // Get the hours, minutes and seconds from the current time
               var hours = now.getHours();
               var minutes = now.getMinutes();
			   var seconds = now.getSeconds();

               // Format hours, minutes and seconds
               if (hours < 10) {
                   hours = "0" + hours;
               }
               if (minutes < 10) {
                   minutes = "0" + minutes;
               }
			   if (seconds < 10) {
					seconds = "0" + seconds;
				}

               // Sets the elements inner HTML value to our clock data
               document.getElementById('time').innerHTML = hours + ':' + minutes + ':' + seconds;
			   
			   setTimeout(updateClock, 250);
           }
		   window.onload = function() {
			updateClock();
			}