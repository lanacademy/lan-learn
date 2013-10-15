// This function gets the current time and injects it into the DOM
           function updateClock() {
               // Gets the current time
               var now = new Date();

               // Get the hours, minutes and seconds from the current time
               var hours = now.getHours();
               var minutes = now.getMinutes();

               // Format hours, minutes and seconds
               if (hours < 10) {
                   hours = "0" + hours;
               }
               if (minutes < 10) {
                   minutes = "0" + minutes;
               }

               // Gets the element we want to inject the clock into
               var elem = document.getElementById('time');

               // Sets the elements inner HTML value to our clock data
               elem.innerHTML = hours + ':' + minutes ;
           }