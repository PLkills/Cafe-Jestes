</main>
<?php $__c = config_loja()['contato'] ?? []; ?>
<footer class="rodape">
  <p>&copy; Café Jester &middot; Projeto Integrador II</p>
  <p class="rodape-info">
    <?php if (!empty($__c['horario'])): ?>Atendimento: <?= e($__c['horario']) ?><br><?php endif; ?>
    <?php if (!empty($__c['whatsapp'])): ?>WhatsApp: <?= e($__c['whatsapp']) ?> &middot; <?php endif; ?>
    <?php if (!empty($__c['email'])): ?><a href="mailto:<?= e($__c['email']) ?>"><?= e($__c['email']) ?></a> &middot; <?php endif; ?>
    <a href="index.php?r=home/privacidade">Privacidade</a> &middot;
    <a href="https://github.com/PLkills/Cafe-Jestes" rel="noopener">Código no GitHub</a>
  </p>
</footer></body></html>
