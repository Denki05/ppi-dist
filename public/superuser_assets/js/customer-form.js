(function () {
  function initStepper(form) {
    var $form = $(form);
    var $steps = $form.find('.cust-step');
    var $fieldsets = $form.find('.cust-fieldset');
    var $line = $form.find('.cust-progress-line');
    var $prev = $form.find('.cust-prev');
    var $next = $form.find('.cust-next');
    var $submit = $form.find('.cust-submit');
    var total = $fieldsets.length;
    var current = 0;

    if (!total) {
      return;
    }

    function render() {
      $fieldsets.removeClass('active').eq(current).addClass('active');
      $steps.removeClass('active activated').each(function (i) {
        if (i < current) {
          $(this).addClass('activated');
        }
        if (i === current) {
          $(this).addClass('active');
        }
      });
      $line.css('width', total > 1 ? (current / (total - 1) * 88) + '%' : '88%');
      $prev.toggle(current > 0);
      $next.toggle(current < total - 1);
      $submit.toggle(current === total - 1);
    }

    function goTo(index) {
      if (index < 0 || index >= total || index === current) {
        return;
      }
      current = index;
      render();
      var top = $form.offset().top - 20;
      if ($(window).scrollTop() > top) {
        $('html, body').stop().animate({ scrollTop: top }, 120);
      }
    }

    $next.on('click', function () {
      goTo(current + 1);
    });

    $prev.on('click', function () {
      goTo(current - 1);
    });

    $steps.on('click', function () {
      goTo(parseInt($(this).data('step'), 10));
    });

    render();
  }

  $(function () {
    $('.cust-form').each(function () {
      initStepper(this);
    });
  });
})();
