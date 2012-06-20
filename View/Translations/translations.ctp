<h1>
    <?php if ($status == Translation::TRANSLATED): ?>
        Translated words
    <?php elseif ($status == Translation::IGNORED): ?>
        Ignored words
    <?php else: ?>
        Untranslated words
    <?php endif; ?>
</h1>
<h2>Search</h2>
<?php echo $this->Form->create('Translation', array('url' => array('controller' => 'translator', 'admin' => true, 'action' => 'translations', $status))); ?>
<?php echo $this->Form->input('msgid', array('label' => 'Text', 'type' => 'text')); ?>
<?php echo $this->Form->input('in_translation', array('label' => 'Search in translations', 'type' => 'checkbox')); ?>
<?php echo $this->Form->submit('Search'); ?>
<?php echo $this->Form->end(); ?>
<h2>Translation list</h2>
<div id="untranslated">
    <?php foreach ($toTranslate as $entry): ?>
        <div class="item" id="<?php echo $entry['Translation']['id']; ?>">
            <?php
            echo $this->Form->create('Translation', array('url' => array(
                    'controller' => 'translator',
                    'action' => 'translate',
                    'admin' => true,
                    'id' => $entry['Translation']['id'],
                )));
            ?>
            <div class="mgsid">
                <div class="tooltip"><?php echo htmlspecialchars($entry['Translation']['msgid']); ?>
                </div>
                <div style="display: none;" class="usings">
                    <?php foreach (unserialize($entry['Translation']['references']) as $ref): ?>
                        <?php echo $ref; ?><br/>
                    <?php endforeach; ?>
                </div>

                <?php /*  echo $this->Form->submit("Save"); */ ?>
            </div>
            <div class="msgstr">

                <?php echo $this->Form->input('msgstr', array('type' => 'textarea', 'label' => false, 'value' => $entry['Translation']['msgstr'])); ?>
                <div style="display:none" class="org"><?php echo $entry['Translation']['msgstr']; ?></div>

            </div>
            <div class="msgign">
                <?php if ($status == Translation::UNTRANSLATED): ?>
                    <span class="pseudolink" onclick="ignoreTrans(<?php echo $entry['Translation']['id']; ?>)">Ignore</span>
                <?php endif; ?>
            </div>
            <?php echo $this->Form->end(); ?>
            <div class="Cleaner"></div>
        </div>

    <?php endforeach; ?>
</div>
<br/>
<div class="paginate">

    <span class="number"><?php echo $this->Paginator->counter('%count%'); ?></span> entries<br/>
    <?php $this->Paginator->options(array('url' => array_merge($this->passedArgs))); ?>
    <?php echo $this->Paginator->prev('« Prev', array('class' => 'next')); ?>
    <?php echo $this->Paginator->numbers(); ?>
    <?php echo $this->Paginator->next('Next »', array('class' => 'next')); ?>
    <br /><br />
    <?php echo $this->Html->link('20 per page', array_merge($this->passedArgs, array('limit' => 20))); ?> |
    <?php echo $this->Html->link('50 per page', array_merge($this->passedArgs, array('limit' => 50))); ?> |
    <?php echo $this->Html->link('100 per page', array_merge($this->passedArgs, array('limit' => 100))); ?>
</div>
<fieldset>
    <legend>See also</legend>
    <ul style="margin-top:1em;">
        <li><?php echo $this->Html->link('Untranslated words', array('admin' => true, 'action' => 'translations')); ?></li>
        <li><?php echo $this->Html->link('Translated words', array('admin' => true, 'action' => 'translations', Translation::TRANSLATED)); ?></li>
        <li><?php echo $this->Html->link('Ignored words', array('admin' => true, 'action' => 'translations', Translation::IGNORED)); ?></li>
        <li><?php echo $this->Html->link('PO files', array('admin' => true, 'action' => 'translation')); ?></li>
        <li><?php echo $this->Html->link('Clear cache', array('action' => 'clear')); ?></li>
    </ul>

</fieldset>


<script type="text/javascript">

    var save_trans_failure_opt = {
        pnotify_title: 'Ups, something goes wrong',
        pnotify_text: '',
        pnotify_delay: 10000,
        pnotify_type: 'error'
    };
    var save_trans_success_opt = {
        pnotify_text: '',
        pnotify_delay: 5000
    }

    var ajaxOptions = {
        success: function(data, status, response, form){
            if(data['ok']){
                $(form).find('textarea').removeClass('modified');
                save_trans_success_opt['pnotify_text'] = 'Translation saved';
                $.pnotify(save_trans_success_opt);
            } else {
                save_trans_failure_opt['pnotify_text'] = data['msg'];
                $.pnotify( save_trans_failure_opt);
            }
        },
        beforeSubmit: function(data, form, options){
            return true;
        },
        dataType: 'json'
    };
    $(function(){
        $('textarea').change(function(){
            $(this).addClass('modified');
            $(this).parents('form').ajaxSubmit(ajaxOptions);
        });
        $("#TranslationMsgid").autocomplete({
            source: "/admin/translator/suggest/in:"+$("#TranslationMsgid").attr('checked'),
            minLength: 2
        });
        $("#TranslationInTranslation").change(function(){
            $("#TranslationMsgid").autocomplete("option", 'source', "/admin/translator/suggest/in:" + $(this).attr('checked'));
        })

    });
    $('#untranslated form').ajaxForm(ajaxOptions);
        

    function ignoreTrans(id){
        url = '<?php echo $this->Html->url(array('admin' => true, 'action' => 'ignore')); ?>/'+id;
        $.getJSON(url, function(data){
            if(data['ok']){
                save_trans_success_opt['pnotify_text'] = 'Added to ignored';
                $.pnotify(save_trans_success_opt);
                $('div#'+data['id']+'.item').slideUp();
            } else {
                save_trans_failure_opt['pnotify_text'] = data['msg'];
                $.pnotify( save_trans_failure_opt);
            }

        });
    }
    
    $('div.tooltip').tipsy({
        title: function(){return $(this).siblings('.usings').html();},
        html: true
    });
    
</script>
