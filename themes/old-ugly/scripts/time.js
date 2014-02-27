// This function gets the current time and injects it into the DOM
           function updateClock() {
               // Gets the current time
               var now = new Date();

               // Get the hours, minutes and seconds from the current time
               var hours = now.getHours();
               var minutes = now.getMinutes();
			   var seconds = now.getSeconds();
			   var date = now.getDate();
			   var month = now.getMonth() + 1;

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
				if (date < 10) {
					date = "0" + date;
				}
				if (month < 10) {
					month = "0" + month;
				}

               // Sets the elements inner HTML value to our clock data
			   document.getElementById('date').innerHTML = date + '.' + month + '.' + now.getFullYear();
               document.getElementById('time').innerHTML =  hours + ':' + minutes + ':' + seconds;
			   
			   setTimeout(updateClock, 250);
           }
		   window.onload = function() {
			updateClock();
			}