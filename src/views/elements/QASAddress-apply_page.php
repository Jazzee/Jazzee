<?php
/**
 * apply_page Standard page type view
 */
$confirm = $this->controller->getVar('confirm');
if ($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED) { ?>
  <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip'; ?>' title='complete this page'>Complete This Page</a> if you wish.</p><?php
} else {
  if (!$page->isRequired() and !count($page->getJazzeePage()->getAnswers())) {?>
    <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip'; ?>' title='skip this page'>Skip This Page</a>.</p><?php
  } ?>
  <div id='counter'><?php
    if ($page->getJazzeePage()->getAnswers()) {
      //infinite answers page
      if (is_null($page->getMax())) {
        if (count($page->getjazzeePage()->getAnswers()) >= $page->getMin()) {?>
          <p>You may add as many additional answers as you wish to this page, but it is not required.</p><?php
        } else { ?>
          <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p><?php
        }
      } else if ($page->getMax() > 1) {
        if ($page->getMax() - count($page->getJazzeePage()->getAnswers()) == 0) {?>
          <p>You have completed this page.</p><?php
        } else if (count($page->getjazzeePage()->getAnswers()) >= $page->getMin()) { ?>
          <p>You may complete an additional <?php print ($page->getMax() - count($page->getJazzeePage()->getAnswers())) ?> answers on this page, but it is not required.</p><?php
        } else { ?>
          <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p><?php
        }
      }
    }?>
  </div><?php
  if ($answers = $page->getJazzeePage()->getAnswers()) { ?>
    <div id='answers'><?php
      foreach ($answers as $answer) { ?>
        <div class='answer<?php
        if ($currentAnswerID and $currentAnswerID == $answer->getID()) {
          print ' active';
        }?>'>
          <h5>Saved Address</h5>
          <p><?php
            $lines = $page->getjazzeePage()->formatAddress($answer);
            foreach ($lines as $line) {
              if (!empty($line)) {
                print $line . '<br />';
              }
            }?>
          </p>
          <p class='status'>
            <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?><?php
            if ($answer->getPublicStatus()) { ?>
              <br />Status: <?php print $answer->getPublicStatus()->getName();
            } ?>
          </p>
          <p class='controls'><?php
          if ($currentAnswerID and $currentAnswerID == $answer->getID()) { ?>
            <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a><?php
          } else { ?>
            <a class='edit' href='<?php print $this->controller->getActionPath(); ?>/edit/<?php print $answer->getId() ?>'>Edit</a>
            <a class='delete' href='<?php print $this->controller->getActionPath(); ?>/delete/<?php print $answer->getId() ?>'>Delete</a><?php
          } ?>
          </p>
        </div><?php
      } //end foreach answers ?>
    </div><?php
  }  //end if answers
  if (!empty($currentAnswerID) or is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax()) {?>
    <div id='leadingText'><?php print $page->getLeadingText() ?></div><?php
    if ($confirm) {
      $picklist = $this->controller->getVar('picklist'); ?>
      <script type = "text/javascript">
        $(document).ready(function(){
          var form = $('<form>')
          .attr('id', 'formPicklist')
          .attr('method', 'post')
          .attr('action','<?php print $this->controller->getActionPath(); ?>/do/pickAddress<?php
          if (!empty($currentAnswerID)) {
            print "/{$currentAnswerID}";
          }?>');
          form.append($('<input>').attr('type', 'hidden').attr('name', 'picklistMoniker').val("<?php echo $picklist->sPicklistMoniker ?>"));
          form.append($('<input>').attr('type', 'hidden').attr('name', 'originalInput').val("<?php print $originalInput ?>"));
          form.append($('<input>').attr('type', 'hidden').attr('name', 'addressMoniker'));
          $('body').append(form);
          var addresses = [];<?php
          foreach ($picklist->atItems as $address) { ?>
            addresses.push({address:"<?php print $address->Picklist; ?>", moniker:"<?php print $address->Moniker; ?>"});<?php
          } ?>
          $.each(addresses, function(i, obj){
            var a = $('<a>').attr('href', '#').html(obj.address);
            a.data('obj', obj);
            a.bind('click', function(e){
              var form = $('#formPicklist');
              $('[name=addressMoniker]', form).val($(e.target).data('obj').moniker);
              form.submit();
              return false;
            });
            $('#pickAddresses').append($('<p>').html(a));
          });
        });
      </script>
      <fieldset>
        <legend>Possible Matches:</legend>
        <div id='pickAddresses'>
          <noscript>
          <form name = "formPicklist" id = "formPicklist" method = "post" action = "<?php print $this->controller->getActionPath(); ?>/do/pickAddress<?php
            if (!empty($currentAnswerID)) {
              print "/{$currentAnswerID}";
            }?>">
            <input type = "hidden" name = 'picklistMoniker' value ="<?php echo $picklist->sPicklistMoniker ?>" />
            <?php $count = 0;
            foreach ($picklist->atItems as $address) { ?>
              <p>
                <input type='radio' name='addressMoniker' id="<?php print "address{$count}"; ?>" value="<?php print $address->Moniker; ?>">
                <label for="<?php print "address{$count}"; ?>"><?php print $address->Picklist; ?></label>
              </p><?php
              $count++;
            } ?>
            <input type='submit' value='Choose Match' />
          </form>
          </noscript>
        </div>
      </fieldset><?php
    } //end if confirm
    $this->renderElement('form', array('form' => $page->getJazzeePage()->getForm())); ?>
    <div id='trailingText'><?php print $page->getTrailingText() ?></div><?php
  }
}