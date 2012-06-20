
<ul>
    <li><?php echo $this->Html->link('Untranslated words', array('admin' => true, 'action' => 'translations')); ?></li>
    <li><?php echo $this->Html->link('Translated words', array('admin' => true, 'action' => 'translations', Translation::TRANSLATED)); ?></li>
    <li><?php echo $this->Html->link('Ignored words', array('admin' => true, 'action' => 'translations', Translation::IGNORED)); ?></li>
</ul>