<template>
  <div>
  <canvas ref="chart" />

  </div>
</template>

<script>
import {Chart, registerables} from 'chart.js'
Chart.register(...registerables)


export default {
    data: () => ({
        chart: null, // register the canvas context. needed to destroy before update
    }),
    computed: {

        data() {
            const datasetConfig = {
                borderWidth: 1,
                fill: false,
                cubicInterpolationMode: 'monotone',
                tension: 0.4
            }
            let {labels, datasets} = {...this.chartdata}

            datasets = datasets.map(dataset => ({...datasetConfig, ...dataset}))

            return {labels, datasets}
        }
    },
    methods: {
        makeChart() {
            const ctx = this.$refs['chart']
            if(!ctx) return
            if(this.chart) this.chart.destroy()
            this.chart = new Chart(ctx, {
                type: 'line',
                data: this.data,
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Entries per day',// Entries per day
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'x'
                    },
                    scales: {
                        x: {
                        title: {
                            display: true,
                                text: 'Day'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Entries'
                            },
                        }
                    }
                }
            })
            return this.chart
        }
    },
    props: {
        chartdata: {
            type: Object,
            default: null
        },
        options: {
            type: Object,
            default: () => ({
                responsive: true,
                maintainAspectRatio: false
            })
        }
    },
    watch: {
        chartdata: {
            immediate: true,
            deep: true,
            handler() {
                this.makeChart()
            }
        }
    }
}
</script>

<style>

</style>