module Auth.Layout exposing (..)

import Dict
import Html exposing (Attribute, Html, a, div, input, label, text)
import Html.Attributes exposing (attribute, class, classList, for, id, value)
import Html.Events exposing (onClick, onInput)
import LaravelElm exposing (Errors)
import Maybe exposing (withDefault)
import Svg exposing (g, polygon, svg)
import Svg.Attributes exposing (fill, viewBox)


authContainer : String -> List (Html msg) -> Html msg
authContainer titleText children =
    div [ class "container relative mx-auto w-full" ]
        [ div [ class "flex flex-wrap justify-center" ]
            [ div [ class "w-full max-w-sm m-4" ]
                [ div [ class "flex flex-col break-words p-6 bg-white shadow border-gray-200" ]
                    ([ div [ class "w-full mb-3 h-16 flex justify-center items-center" ]
                        [ div [ class "w-16" ] [ logo ]
                        ]
                     , div [ class "mb-3" ]
                        [ text titleText ]
                     ]
                        ++ children
                    )
                ]
            ]
        ]


authInput : (String -> msg) -> String -> Errors -> String -> String -> List (Attribute msg) -> Html msg
authInput msg inputValue errors labelValue nameValue attrs =
    div [ class "w-full" ]
        [ label [ class "block text-sm mb-2", for nameValue ]
            [ text labelValue ]
        , input ([ onInput msg, value inputValue, class "shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline", classList [ ( "border-red-500", Dict.get nameValue errors |> Maybe.withDefault [] |> List.isEmpty |> not ) ], id nameValue ] ++ attrs) []
        ]


authErrors : Errors -> String -> Html msg
authErrors errors nameValue =
    div [ class "text-red-500 text-xs italic mt-4" ]
        (List.map (\error -> div [] [ text error ]) (Dict.get nameValue errors |> withDefault []))


authStatus : Maybe String -> Html msg
authStatus maybeStatus =
    case maybeStatus of
        Just status ->
            div [ class "text-indigo-700 mb-2" ]
                [ text status
                ]

        Nothing ->
            text ""


authButtonClasses : String
authButtonClasses =
    "flex items-center text-white py-2 px-4 rounded bg-indigo-700"


authLink : msg -> String -> List (Html msg) -> Html msg
authLink clickMsg classes children =
    a [ onClick clickMsg, class "inline-block text-indigo-700 cursor-pointer word-break", class classes ] children


logo : Html msg
logo =
    svg [ viewBox "0 0 266 270" ]
        [ g [ fill "none", attribute "fill-rule" "evenodd", attribute "stroke" "none", attribute "stroke-width" "1" ]
            [ g [ attribute "transform" "translate(-193.000000, -96.000000)" ]
                [ g [ attribute "transform" "translate(205.000000, 110.000000)" ]
                    [ polygon [ fill "#FACD66", attribute "points" "47 155.666667 47 83 192 83 192 155.666667 119.5 192" ]
                        []
                    , polygon [ fill "#D99511", attribute "points" "120 83 192 83 192 155.666667 120 192" ]
                        []
                    , polygon [ fill "#EDB024", attribute "points" "47 83.5 119.5 47 192 83.5 119.5 120" ]
                        []
                    , g [ attribute "opacity" "0.445382254" ]
                        [ polygon [ fill "#FACD66", attribute "points" "0 180 0 60 120 120 120 180 120 240" ]
                            []
                        , polygon [ fill "#D99511", attribute "points" "120 120 240 60 240 180 120 240" ]
                            []
                        , polygon [ fill "#EDB024", attribute "points" "0 60 35.3590414 42.3204793 92.7424858 13.6287571 120 0 240 60 120 120 66.5768909 93.2884455" ]
                            []
                        ]
                    ]
                ]
            ]
        ]
