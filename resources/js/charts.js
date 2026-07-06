import ApexCharts from "apexcharts";
window.ApexCharts = ApexCharts;

document.addEventListener("alpine:init", () => {
    Alpine.data("weatherChart", (initial) => ({
        chart: null,

        isDark() {
            return document.documentElement.classList.contains("dark");
        },

        themeColors() {
            const dark = this.isDark();
            return {
                label:  dark ? "#a3a3a3" : "#64748b",
                grid:   dark ? "#404040" : "#f1f5f9",
                tooltip: dark ? "dark" : "light",
                legend: dark ? "#c2c2c2" : "#334155",
            };
        },

        // Satuan pendek untuk label di atas titik (°C → °, sisanya tanpa spasi)
        shortUnit(unit) {
            return unit === "°C" ? "°" : "";
        },

        buildOptions(data) {
            const c = this.themeColors();
            return {
                chart: {
                    type: "area",
                    height: 300,
                    fontFamily: "inherit",
                    toolbar: { show: false },
                    animations: { enabled: true, easing: "easeinout", speed: 400 },
                    background: "transparent",
                },
                theme: { mode: this.isDark() ? "dark" : "light" },
                stroke: { curve: "smooth", width: 2.5 },
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.02,
                        stops: [0, 100],
                    },
                },
                colors: [data.color],
                series: [{ name: `${data.label} (${data.unit})`, data: data.data }],
                xaxis: {
                    categories: data.categories,
                    labels: { style: { colors: c.label, fontSize: "11px" } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    tooltip: { enabled: false },
                },
                // Sembunyikan y-axis — angka tampil langsung di atas titik (ala Google)
                yaxis: { show: false },
                legend: { show: false },
                // Grid minimal: tanpa garis, hanya beri ruang untuk label di atas
                grid: {
                    show: false,
                    padding: { top: 24, right: 8, left: 8, bottom: 0 },
                },
                tooltip: {
                    theme: c.tooltip,
                    y: { formatter: (val) => `${val} ${data.unit}` },
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => `${val}${this.shortUnit(data.unit)}`,
                    offsetY: -8,
                    background: { enabled: false },
                    dropShadow: { enabled: false },
                    style: {
                        fontSize: "11px",
                        fontWeight: 600,
                        colors: [c.label],
                    },
                },
                markers: { size: 3, strokeWidth: 0, hover: { size: 5 } },
            };
        },

        init() {
            this.chart = new ApexCharts(this.$refs.chart, this.buildOptions(initial));
            this.chart.render();

            // Re-theme saat toggle dark/light
            this.observer = new MutationObserver(() => {
                const c = this.themeColors();
                this.chart.updateOptions({
                    theme: { mode: this.isDark() ? "dark" : "light" },
                    xaxis: { labels: { style: { colors: c.label } } },
                    dataLabels: { style: { colors: [c.label] } },
                    tooltip: { theme: c.tooltip },
                }, false, false);
            });
            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"],
            });

            // Update chart saat Livewire push data baru
            Livewire.on("chart-updated", ({ chart }) => {
                this.chart.updateOptions({
                    colors: [chart.color],
                    xaxis: { categories: chart.categories },
                    dataLabels: {
                        formatter: (val) => `${val}${this.shortUnit(chart.unit)}`,
                    },
                    tooltip: {
                        y: { formatter: (val) => `${val} ${chart.unit}` },
                    },
                }, false, true);
                this.chart.updateSeries([
                    { name: `${chart.label} (${chart.unit})`, data: chart.data },
                ]);
            });
        },
    }));
});
