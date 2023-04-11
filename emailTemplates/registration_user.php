Dear {%username%}
<br />
<br />
Thank you for registering on our <a href="{%linkToScript%}">file management system</a>. Here is your information: <br />
<br />Username: {%username%}
<br />Password: {%password%}
<br />Email: {%email%}
<?php if ($_auto_approve) {?>
<br />Status: automatically approved!
<?php } else {?>
<br />Status: NOT APPROVED! Administrator will review and approve your application shortly.
<?php }?>
<br />
<br />
Thank you.