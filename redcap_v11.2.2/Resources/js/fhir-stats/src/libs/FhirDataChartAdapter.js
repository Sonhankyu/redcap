const COLORS = {
    red_alt1:    [255, 99, 132],
    blue_alt1:   [54, 162, 235],
    yellow_alt1: [255, 206, 86],
    green_alt1:  [75, 192, 192],
    purple_alt1: [153, 102, 255],
    orange_alt1: [255, 159, 64],
    teal_alt1:   [32, 201, 151],
    cyan_alt1:   [23, 162, 184],
    // 'pure' colors from here
    lime:       [0,255,0],
    aqua:       [0,255,255],
    maroon:     [128,0,0],
    navy:       [0,0,128],
    olive:      [128,128,0],
    silver:     [192,192,192],
    blue:       [0,0,255],
    fuchsia:    [255,0,255],
    green:      [0,128,0],
    teal:       [0,128,128],
    purple:     [128,0,128],
    gray:       [128,128,128],
    red:        [255,0,0],
    yellow:     [255,255,0],
    black:      [0,0,0],
    white:      [255,255,255],
}

/**
 * class that generates CSS valid rgba colors
 */
const ColorMaker = class {
    
    /**
     * helper method to create a list of CSS color strings
     * with custom opacity
     */
     makeCssColor(color, opacity=1) {
        opacity = parseFloat(opacity)
        return `rgba(${color.join(', ')}, ${opacity})`
    }
    makeCssColors(opacity) {
        // curried function will be used in map
        const makeCssColor = (color) => this.makeCssColor(color, opacity)
        return Object.values(COLORS).map(makeCssColor)
    }
    getRandomColor(value, opacity=0.2) {
        opacity = parseFloat(opacity)
        //value from 0 to 1
        value += 1
        var max = 200
        var red = Math.round( (value  * Math.random() * 200 ) )% max
        var green = Math.round( (value * Math.random() * 200 ) )% max
        var blue = Math.round( (value * Math.random() * 200 ) )% max
        var colors = [red, green, blue, opacity].join(', ')
        return 'rgba('+colors+')'
        // hlsa version
        // var hue=((value+200)*20).toString(10)
        // return ["hsla(",hue,",100%,50%, .2)"].join("")
    }
}

const FhirDataChartAdapter = class {

    constructor() {
        this.colorMaker = new ColorMaker()
    }
    
    
    /**
     * format a 'total' type of data
     * received from the API.
     * 
     */
    makeTotalChartData = (data, label='') => {
        const chartData = {
            labels: Object.keys(data),
            datasets: [{
                data, label,
                backgroundColor: this.colorMaker.makeCssColors(0.2),
                borderColor: this.colorMaker.makeCssColors(1),
                borderWidth: 1,
            }]
        }
        return chartData
    }
    
     /**
      * create the config options for the daily graph
      */
     makeDailyChartData = (dailyData) => {
        const groupDataByResource = (dailyData) => {
            var datasets = {}
            for (let data of Object.values(dailyData)) {
                for (let [type, counts] of Object.entries(data)) {
                    if (!datasets[type]) datasets[type] = []
                    datasets[type].push(counts)
                }
            }
            return datasets
        }
        var dataByResource = groupDataByResource(dailyData)
        var labels = Object.keys(dailyData)
        var datasets = []
        var counter = 0 // use this index to pick a color
        var backgroundColors = this.colorMaker.makeCssColors(0.2)
        var borderColors = this.colorMaker.makeCssColors(1)
        for (let [type, data] of Object.entries(dataByResource)) {
            let colorIndex = counter++
            var dataset = {
                label: type,
                backgroundColor: backgroundColors[colorIndex],
                borderColor: borderColors[colorIndex],
                fill: false,
                data: data, //set the data
            }
            datasets.push(dataset)
        }
        const chartData = { labels,datasets }
        return chartData
     }
}

export { FhirDataChartAdapter as default, ColorMaker, COLORS }