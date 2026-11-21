/**
 * Copyright (C) 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.
 * Proprietary and confidential. Unauthorized copying or distribution is strictly prohibited.
 * Author: Adham Cherkaoui Eddeqaqi (0dh4m) <0dh4mm@gmail.com>
 * GitHub: https://github.com/0dh4m
 */

﻿using System;

namespace HappyGymProApp.Services
{
    public class MacroResult
    {
        public int Calories { get; set; }
        public int Protein { get; set; }
        public int Carbs { get; set; }
        public int Fat { get; set; }
    }

    public static class MacroCalculator
    {
        public static MacroResult Calculate(double weight, double height, int age, string gender, double activity, string goal)
        {
            // معادلة Mifflin-St Jeor
            double bmr = (10 * weight) + (6.25 * height) - (5 * age) + (gender == "male" ? 5 : -161);
            double tdee = bmr * activity;
            double targetCalories = tdee;
            double protein = 0;

            if (goal == "bulking")
            {
                targetCalories = tdee + 500;
                protein = weight * 2.2;
            }
            else if (goal == "cutting")
            {
                targetCalories = tdee - 500;
                protein = weight * 2.4;
            }
            else
            {
                targetCalories = tdee;
                protein = weight * 2.0;
            }

            double fat = (targetCalories * 0.25) / 9;
            double carbs = (targetCalories - ((protein * 4) + (fat * 9))) / 4;

            return new MacroResult
            {
                Calories = (int)Math.Round(targetCalories),
                Protein = (int)Math.Round(protein),
                Carbs = (int)Math.Round(carbs),
                Fat = (int)Math.Round(fat)
            };
        }
    }
}