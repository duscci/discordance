(function () {
    const whs = document.querySelector("#webhooks")
    const format = document.querySelector("#format")
    const format_warn = document.querySelector("#format-warn")
    const submit = document.querySelector("#submit")
    if (whs) {
        whs.addEventListener("keyup", function (e) {
            e.preventDefault()
            whs.value = whs.value.replace(/\s|\,/g, "\n")
        })
    }
    if (format) {
        format.addEventListener("keyup", function (e) {
            e.preventDefault()
            try {
                JSON.parse(format.value)
                format_warn.innerHTML = ""
                if (submit.hasAttribute("disabled")) {
                    submit.removeAttribute("disabled")
                }
            } catch (err) {
                format_warn.innerHTML = "This format is not a valid JSON"
                if (!submit.hasAttribute("disabled")) {
                    submit.setAttribute("disabled", true)
                }
            }
        })
    }
})()