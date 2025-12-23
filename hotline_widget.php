<!-- Hotline Widget -->
<div class="hotline-widget" id="hotlineWidget">
    <div class="hotline-toggle" onclick="toggleHotlineWidget()">
        <span class="glyphicon glyphicon-earphone"></span>
    </div>
    <div class="hotline-content" id="hotlineContent">
        <div class="hotline-header">
            <h4>Liên Hệ</h4>
            <button class="hotline-close" onclick="toggleHotlineWidget()">&times;</button>
        </div>
        <div class="hotline-body">
            <a href="tel:0854008327" class="hotline-item">
                <span class="glyphicon glyphicon-earphone"></span>
                <span>Hotline: 0854008327</span>
            </a>
            <a href="https://www.facebook.com/TQNfigure" target="_blank" class="hotline-item">
                <span class="glyphicon glyphicon-user"></span>
                <span>Facebook</span>
            </a>
            <a href="mailto:quynhnhu255910@gmail.com" class="hotline-item">
                <span class="glyphicon glyphicon-envelope"></span>
                <span>Email</span>
            </a>
        </div>
    </div>
</div>

<script type="text/javascript">
    function toggleHotlineWidget() {
        var widget = document.getElementById('hotlineWidget');
        var content = document.getElementById('hotlineContent');
        if (widget) {
            widget.classList.toggle('active');
        }
    }
</script>

