﻿using System;
using System.Collections.Generic;

namespace Net.XpFramework.Runner
{
    class Xcc : BaseRunner
    {

        static void Main(string[] args)
        {
            Execute("class", "xp.compiler.Main", new string[] { "." }, args);
        }
    }
}
