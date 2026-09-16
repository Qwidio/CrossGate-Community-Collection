# Technical journals
I planned to replace the current name from CGCC (CrossGate Community Collection) with a new name but cancelled it given the time it took to redesign logo and naming will take a longer time, make petition on the website if anyone wanted me to give a go. yeah I know it's not more technical that the other journals, this is made just to separate the older journal.

## updates
What have been updated since the last major update, as much as the given time constraint.

### Upgraded auth backends
  I cannot believe that I'm using such a vulnerable password encryption for a long time without realizing how much dangerous it is if I actually did host this with my own server and I did makes support for legacy MD5 just in case that like many times before, the server hosting it started acting up again.
  Now the web registration, login and auth api now use password hash with the registration also added confirmation email activation when creating an account but this one has not been tested yet. 

### Added the option to reset account
  I already dig into the auth again so I might as well makes it that I can reset password when needed because previously there's no way to change password once the account registered unless directly changed  

### Updated auth frontends
  Updated the device detector, the output are still same as the main difference that it uses the newer navigator.userAgentData <br>
  Modified the UI and input for more seamless experience, replace dm link with an open forum option

### Upgraded Groups-Flow backends
  Applying the same password hash method from account login and registration, Modified the reset access acount passkeys with the password hashing.

### updated logout script
  just making sure that `sessionToken` cookie get resetted everytime even if use didn't logged in with one.

### Library view page updates
  Fixed markdown renderer frequently not properly rendering. Added new type of redirecting link for `prms` view type. Added collection status card that display information about the development status, notes from developer/publisher and downloadDisabled status. 

### Better MarkOut process
  `/processes/markout.php` was missing the collection markedout number updater, given that this process can be abused easily I decided to overhaul the entire processes and implement transaction protection on both table locking the queried rows while updating the data so that there will be no lost count if two update request happen at the same time.

### allow for disabling download
  Modified `/api/download.php` with `downloadDisabled` parameters to only allow download if the value set to 0.
  Collection Manager updated with checkbox on the edit form to disabled download, If set to disabled user will be noticed on the collection view page that the download is disabled.
  Modified the client launcher to make sure user can't download when download disabled set to true and instead display 'unavailable' text on the download button

### New profile borders and theme
  Other than removing unnecessary query check on the profile page is I added one more attribute to user prefs and one more forms, I might not mention this but the way I make the settings page really helps makes new attribute editting easier albeit can be more efficient and less nested.

### fixed bug related to empty images input
  Some older bug left since the last major changes, Passing a string value directly to the `bind_param` function. Now it should be fine when for example creating badges without image attachment, some upload code were also updated to use "empty" value when no image input found 
  

### smaller but still important improvement
 - Added `.gitignore` to exclude non essential asset being commited and `.dockerignore` for setup on self hosted container 
 - Removed the redundant groups query check on some page like `/legal/copyright.php`
 - Changed banner aspect ratio to 10/3 on `/index.php`
 - Updated form uniLoad function to support checkboxes on `/scriptstuff/script.js`
 - Removed unused classes and added new background classes for the new theme 
 - Updated documentation following the new feature like the `downloadDisabled` property