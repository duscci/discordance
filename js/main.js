(function () {
    const form = document.querySelector("form")
    const webhooks = document.querySelector("#webhooks")
    const format_warn = document.querySelector("#format-warn")
    const format = document.querySelector("#format")
    const pretty = document.querySelector("#pretty")
    const submit = document.querySelector("#submit")
    if (webhooks) {
        webhooks.addEventListener("keyup", function (e) {
            e.preventDefault()
            webhooks.value = webhooks.value
                .replace(/\s|\,/g, "\n")
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
    if (pretty) {
        pretty.addEventListener("click", function (e) {
            e.preventDefault()
            try {
                format.value = JSON.stringify(JSON.parse(format.value), null, 4)
            } catch (err) {
                return
            }
        })
    }
    if (form) {
        form.addEventListener("submit", function (e) {
            const valid_webhooks = new Set()
            webhooks.value
                .split("\n")
                .filter(v => /^(https\:\/\/(www\.)?discord\.com\/api\/webhooks\/([0-9]+)\/([a-zA-Z0-9_-]+))/.test(v))
                .map(v => valid_webhooks.add(v))
            webhooks.value = Array.from(valid_webhooks).join("\n")
            pretty.click()
            return true
        })
    }
})()