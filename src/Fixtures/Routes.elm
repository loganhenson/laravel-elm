port module Routes exposing (..)


import Json.Decode exposing (Value)


port get : String -> Cmd msg


port post : Value -> Cmd msg


port delete : String -> Cmd msg

ROUTES
