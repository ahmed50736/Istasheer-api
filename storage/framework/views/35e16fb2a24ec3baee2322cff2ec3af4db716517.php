<html>
    <div style="font-family: Helvetica,Arial,sans-serif;min-width:1000px;overflow:auto;line-height:2">
        <div style="margin:50px auto;width:70%;padding:20px 0">
          <div style="border-bottom:1px solid #eee">
            <a href="https://istesheer.com" style="font-size:1.4em;color: #00466a;text-decoration:none;font-weight:600">Istesheer</a>
          </div>
          <p style="font-size:1.1em">Dear <?php echo e($username); ?>,</p>
          <p style="font-size:1.1em">Your Credentails changed by admin, here is your new credentails for login</p>
          <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">UserName:<?php echo e($username); ?></h2><br>
          <h2 style="background: #00466a;margin: 0 auto;width: max-content;padding: 0 10px;color: #fff;border-radius: 4px;">Password:<?php echo e($password); ?></h2><br>
          <p>Please do not share this credentials with anyone</p>
          <p style="font-size:0.9em;">Regards,<br />Istesheer Team</p>
          <hr style="border:none;border-top:1px solid #eee" />
          <div style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
           
          </div>
        </div>
      </div>
</html><?php /**PATH /media/hassan/Projects/Personal work/client work/aloadaiman-app-test-work/resources/views/emails/credentilasmailer.blade.php ENDPATH**/ ?>