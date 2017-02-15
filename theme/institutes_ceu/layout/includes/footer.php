<footer id="page-footer" class="page-footer">
    <div id="course-footer"><?php echo $OUTPUT->course_footer(); ?></div>
    <p class="helplink"><?php echo $OUTPUT->page_doc_link(); ?></p>
    <div class="foot-notes">
        <div class="rotate-white"></div>
        <div class="rotate-red"></div>
        <?php echo $html->footnote; ?>
    </div>
    <div class="footer-info">
        <?php
        echo $OUTPUT->login_info();
        echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        ?>
    </div>
</footer>