<h1>Manage translations</h1>

<ul>
    <li><?php echo $this->Html->link('Download current PO file', 'download/po'); ?></li>
</ul>

<h2>Upload new translation file</h2>
<?php echo $this->Form->create('Config', array('type' => 'file', 'url' => '/admin/translator/upload_translation')); ?>

<?php echo $this->Form->input('translation', array('label' => 'Translation file: ', 'type' => 'file')); ?>
<?php echo $this->Form->end('Upload'); ?>