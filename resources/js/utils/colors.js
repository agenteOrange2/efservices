const helper = require("./helper");
const resolveConfig = require("tailwindcss/resolveConfig");
const tailwindConfig = require("../../tailwind.config"); // Asegúrate de que la ruta sea correcta
const flatten = require("flat");

const twConfig = resolveConfig(tailwindConfig);
const colors = twConfig.theme?.colors;

function getColor(colorKey, opacity = 1) {
    const flattenColors = flatten(colors);

    if (!flattenColors[colorKey]) {
        console.warn(`Color key "${colorKey}" not found in theme colors.`);
        return `rgb(0, 0, 0 / ${opacity})`; // Color de fallback
    }

    if (flattenColors[colorKey].search("var") === -1) {
        return `rgb(${helper.toRGB(flattenColors[colorKey])} / ${opacity})`;
    } else {
        const cssVariableName = `--color-${
            flattenColors[colorKey].split("--color-")[1].split(")")[0]
        }`;
        return `rgb(${getComputedStyle(document.body).getPropertyValue(
            cssVariableName
        )} / ${opacity})`;
    }
}

// Exporta la función para que pueda ser usada en otros módulos
module.exports = { getColor };
