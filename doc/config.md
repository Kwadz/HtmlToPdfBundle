Configuration Reference
=======================

    jihel_plugin_html_to_pdf:
        constants:
            - DIRECTORY_SEPARATOR
        commands:
            dpi: 150
            wkhtmltopdf: '/usr/bin/wkhtmltopdf'
            wkhtmltopdf_args: '-T 0px -L 0px -B 0px -R 0px'
            xvfb: '/usr/bin/xvfb-run'
            xvfb_args: '--auto-servernum --server-args="-screen 0, 1920x1024x24"'
            concatenate: '/usr/bin/pdftk'
            concatenate_args: ~
        tmp_folder: '/tmp'
        tmp_prefix: 'jihel_pdf-'
        use_xvfb: true
        quiet_mode: false

- constants:
    You can add all constants you want to access in all you pdf here.
- commands:
    - dpi: The pdf dpi resolution
    - wkhtmltopdf: Path to wkhtmltopdf binary
    - wkhtmltopdf_args: Base arguments of wkhtmltopdf. You can have an ehaustive list with the `man wkhtmltopdf` command
    - xvfb: Path to xvfb binary
    - xvfb_args: Base arguments of xvfb. You can have an ehaustive list with the `man xvfb` command
    - concatenate: Path to pdftk binary
    - concatenate_args: Base arguments of pdftk. You can have an ehaustive list with the `man pdftk` command
- tmp_folder: Path to the tmp folder
- tmp_prefix: Temp files naming is `[tmp_prefix][date('YmdHis_')][uniqid()].[extention]`
- use_xvfb: Use the xvfb xserver instance or not
- quiet_mode: Send command output to /dev/null
